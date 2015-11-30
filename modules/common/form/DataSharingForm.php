<?php

/**
 * Openbizx Cubi Application Platform
 *
 * LICENSE http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 *
 * @package   cubi.common.form
 * @copyright Copyright (c) 2005-2011, Openbiz Technology LLC
 * @license   http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 * @link      http://code.google.com/p/openbiz-cubi/
 * @version   $Id: DataSharingForm.php 4612 2012-11-06 04:34:26Z hellojixian@gmail.com $
 */
use Openbizx\Openbizx;
use Openbizx\Data\DataRecord;
use Openbizx\Easy\EasyForm;

class DataSharingForm extends EasyForm
{

    public $hasOwnerField = false;
    public $dataRecordName;
    protected $logDO = "changelog.do.ChangeLogDO";

    public function SetPrtRecordId($id)
    {

        if ($id) {
            $this->parentRecordId = $id;
        }
        return;
    }

    public function fetchData()
    {
        if ($this->activeRecord != null) {
            return $this->activeRecord;
        }


        $prtForm = $this->parentFormName;
        $prtFormObj = Openbizx::getObject($prtForm);
        if (!$prtForm) {
            return array();
        }

        $this->SetPrtRecordId($this->recordId);

        $recId = $this->parentRecordId;
        $dataObj = $prtFormObj->getDataObj();
        $dataRec = $dataObj->fetchById($recId);

        $user_id = Openbizx::$app->getUserProfile("Id");
        $group_id = Openbizx::$app->getUserProfile("default_group");


        $this->hasOwnerField = $this->_hasOwnerField();

        $result = array();
        $result['Id'] = $dataRec['Id'];
        $result['editable'] = 0;
        $result['has_ref_data'] = 0;

        if ($dataObj->objReferences->count()) {
            $result['has_ref_data'] = 1;
        }

        if ($user_id == $dataRec['create_by']) {
            $result['shared_data'] = 0;
            $result['editable'] = 1;
        } elseif ($this->hasOwnerField && $owner_id == $user_id) {
            $result['shared_data'] = 0;
            $result['editable'] = 1;
        } elseif ($group_id == $dataRec['group_id']) {
            $result['shared_data'] = 1;
        } else {
            $result['shared_data'] = 2;
        }

        if ($dataRec['name'] != '') {
            $result['data_record'] = $dataRec['name'];
        } elseif ($dataRec['subject'] != '') {
            $result['data_record'] = $dataRec['subject'];
        } elseif ($dataRec['title'] != '') {
            $result['data_record'] = $dataRec['title'];
        } elseif ($dataRec['display_name'] != '') {
            $result['data_record'] = $dataRec['display_name'];
        } else {
            $result['data_record'] = $dataRec['Id'];
        }
        $this->dataRecordName = $result['data_record'];

        if ($this->hasOwnerField) {
            $owner_id = $dataRec['owner_id'];
            $result['owner_id'] = $dataRec['owner_id'];

            if ($dataRec['owner_id'] != $dataRec['create_by']) {
                if ($dataRec['owner_id'] == $user_id) {
                    $result['shared_data'] = 3;
                    $result['editable'] = 1;
                } elseif ($dataRec['create_by'] == $user_id) {
                    $result['shared_data'] = 4;
                    $result['editable'] = 1;
                }
            }
        } else {
            $owner_id = $dataRec['create_by'];
        }

        $result['data_record'] = str_replace("<br />", "", $result['data_record']);
        $result['owner_perm'] = 3;
        $result['create_by'] = $dataRec['create_by'];

        $inputArr = $this->readInputRecord();

        $result['group_id'] = $dataRec['group_id'];
        $result['group_perm'] = isset($inputArr['group_perm']) ? $inputArr['group_perm'] : $dataRec['group_perm'];
        $result['other_perm'] = isset($inputArr['other_perm']) ? $inputArr['other_perm'] : $dataRec['other_perm'];
        $result['group_name'] = $this->_getGroupName($dataRec['group_id']);
        $result['owner_name'] = $this->_getOwnerName($owner_id);
        $result['creator_name'] = $this->_getOwnerName($dataRec['create_by']);
        $result['hasOwnerField'] = (int) $this->hasOwnerField;

        $result['form_title'] = $prtFormObj->title;
        $result['action_timestamp'] = date("Y-m-d H:i:s");
        $result['refer_url'] = SITE_URL;

        if ($result['editable'] == 0) {
            $svcObj = Openbizx::getService(OPENBIZ_DATAPERM_SERVICE);
            $result['editable'] = (int) $svcObj->checkDataPerm($dataRec, 3, $dataObj);
        }

        if ($result['editable'] == 0) {
            $result['has_ref_data'] = 0;
        }
        $this->recordId = $result['Id'];
        $this->parentRecordId = $result['Id'];
        //$this->setActiveRecord($result);
        if (Openbizx::$app->allowUserAccess("data_manage.manage")) {
            $result['editable'] = 1;
            $result['data_manage'] = 1;
        } else {
            $result['data_manage'] = 0;
        }
        return $result;
    }

    protected function setActiveRecord($record)
    {

        $this->activeRecord = $this->fetchData();
        if (is_array($record)) {
            foreach ($record as $key => $value) {
                $this->activeRecord[$key] = $record[$key];
            }
        }
    }

    public function ShareRecord()
    {
        $prtForm = $this->parentFormName;
        if (!$prtForm) {
            return;
        }
        $prtFormObj = Openbizx::getObject($prtForm);
        $recId = $this->parentRecordId;
        $dataObj = $prtFormObj->getDataObj();
        $dataRec = $dataObj->fetchById($recId);

        $recArr = $this->readInputRecord();
        $DataRec = $dataRec;
        $DataRecOld = $dataRec;
        $currentRecord = $DataRecOld->toArray();

        //notice users has new shared data
        //test if changed a new owner
        if ($recArr['notify_user']) {
            $data = $this->fetchData();
            $data['app_index'] = OPENBIZ_APP_INDEX_URL;
            $data['app_url'] = OPENBIZ_APP_URL;
            $data['operator_name'] = Openbizx::$app->getProfile()->getProfileName(Openbizx::$app->getUserProfile("Id"));

            $emailSvc = Openbizx::getService(CUBI_USER_EMAIL_SERVICE);
            if ($DataRec['owner_id'] != $recArr['owner_id']) {
                $emailSvc->DataAssignedEmail($recArr['owner_id'], $data);
            }

            //test if changes for group level visiable
            if ($recArr['group_perm'] >= 1) {
                $group_id = $recArr['group_id'];
                $userList = $this->_getGroupUserList($group_id);
                foreach ($userList as $user_id) {
                    $emailSvc->DataSharingEmail($user_id, $data);
                }
            }
            //test if changes for other group level visiable
            if ($recArr['other_perm'] >= 1) {

                $groupList = $this->_getGroupList();
                foreach ($groupList as $group_id) {
                    if ($recArr['group_id'] == $group_id) {
                        continue;
                    }
                    $userList = $this->_getGroupUserList($group_id);
                    foreach ($userList as $user_id) {
                        $emailSvc->DataSharingEmail($user_id, $data);
                    }
                }
            }
        }

        if (isset($recArr['group_perm'])) {
            $DataRec['group_perm'] = $recArr['group_perm'];
        }

        if (isset($recArr['other_perm'])) {
            $DataRec['other_perm'] = $recArr['other_perm'];
        }

        if (isset($recArr['group_id'])) {
            $DataRec['group_id'] = $recArr['group_id'];
        }

        if (isset($recArr['owner_id'])) {
            $DataRec['owner_id'] = $recArr['owner_id'];
        }

        if (isset($recArr['create_by'])) {
            $DataRec['create_by'] = $recArr['create_by'];
            $DataRec['update_by'] = $recArr['create_by'];
            $DataRec['update_time'] = date('Y-m-d H:i:s');
        }

        $DataRec->save();
        $inputRecord = $recArr;
        //$prtFormObj->getDataObj()->updateRecord($newDataRec,$dataRec);
        //save change log
        $postFields = $_POST;
        $elem_mapping = array();
        foreach ($postFields as $elem_name => $value) {
            $elem = $this->dataPanel->get($elem_name);
            $fld_name = $elem->fieldName;
            if ($elem) {
                $elem_mapping[$fld_name] = $elem;
            }
        }
        $logDO = $dataObj->getRefObject($this->logDO);
        if ($logDO) {

            $cond_column = $logDO->association['CondColumn'];
            $cond_value = $logDO->association['CondValue'];

            if ($cond_column) {
                $type = $cond_value;
            }
            $foreign_id = $currentRecord['Id'];
            $logRecord = array();




            foreach ($inputRecord as $fldName => $fldVal) {
                $oldVal = $currentRecord[$fldName];
                if ($oldVal == $fldVal)
                    continue;

                if ($oldVal === null || $fldVal === null)
                    continue;

                $elem = $elem_mapping[$fldName]->xmlMeta;
                if (!$elem) {
                    $elem = $this->dataPanel->getByField($fldName)->xmlMeta;
                }
                $logRecord[$fldName] = array('old' => $oldVal, 'new' => $fldVal, 'element' => $elem);
            }
            $formMetaLite = array(
                "name" => $this->objectName,
                "package" => $this->package,
                "message_file" => $this->messageFile,
            );

            // save to comment do
            $logRec = new DataRecord(null, $logDO);
            $logRec['foreign_id'] = $foreign_id;
            $logRec['type'] = $type;
            $logRec['form'] = serialize($formMetaLite);
            $logRec['data'] = serialize($logRecord);
            $logRec['comment'] = $comment;
            $logRec->save();
        }
        //end save change log

        if ($recArr['update_ref_data']) {
            if ($dataObj->objReferences->count()) {
                $this->_casacadeUpdate($dataObj, $recArr);
            }
        }


        if ($this->parentFormName) {
            $this->close();
            $this->renderParent();
        }
        $this->processPostAction();
    }

    protected function _getGroupList()
    {
        $rs = Openbizx::getObject("system.do.GroupDO")->directFetch("");
        $group_ids = array();
        foreach ($rs as $group) {
            $group_ids[] = $group['Id'];
        }
        return $group_ids;
    }

    protected function _getGroupUserList($group_id)
    {
        $rs = Openbizx::getObject("system.do.UserGroupDO")->directFetch("[group_id]='$group_id'");
        $user_ids = array();
        foreach ($rs as $user) {
            $user_ids[] = $user['user_id'];
        }
        return $user_ids;
    }

    private function _casacadeUpdate($obj, $setting)
    {
        $dataShareSvc = Openbizx::getService(OPENBIZ_DATAPERM_SERVICE);
        foreach ($obj->objReferences as $doRef) {
            $do = Openbizx::getObject($doRef->objectName);
            $rs = $do->fetch();
            foreach ($rs as $rec) {
                if ($dataShareSvc->checkDataOwner($rec)) {
                    $newRec = $rec;
                    $newRec['group_perm'] = $setting['group_perm'];
                    $newRec['other_perm'] = $setting['other_perm'];
                    $newRec['group_id'] = $setting['group_id'];
                    if ($rec['owner_id']) {
                        $newRec['owner_id'] = $setting['owner_id'];
                    }
                    $ok = $do->updateRecord($newRec, $rec);
                }
            }
            if ($do->objReferences->count()) {
                //$this->_casacadeUpdate($do, $setting);
            }
        }
    }

    private function _getGroupName($id)
    {
        $rec = Openbizx::getObject("system.do.GroupDO")->fetchById($id);
        $result = $rec['name'];
        return $result;
    }

    private function _getOwnerName($id)
    {
        $result = Openbizx::$app->getProfile()->getProfileName($id);
        return $result;
    }

    private function _hasOwnerField()
    {
        $prtForm = $this->parentFormName;
        $prtFormObj = Openbizx::getObject($prtForm);
        $field = $prtFormObj->getDataObj()->getField('owner_id');
        if ($field) {
            return true;
        } else {
            return false;
        }
    }

    public function loadStatefullVars($sessionContext)
    {
        $sessionContext->loadObjVar("DataSharingForm", "ParentRecordId", $this->parentRecordId);
        $sessionContext->loadObjVar("DataSharingForm", "ParentFormName", $this->parentFormName);
        return parent::loadStatefullVars($sessionContext);
    }

    public function saveStatefullVars($sessionContext)
    {
        $sessionContext->saveObjVar("DataSharingForm", "ParentRecordId", $this->parentRecordId);
        $sessionContext->saveObjVar("DataSharingForm", "ParentFormName", $this->parentFormName);
        return parent::saveStatefullVars($sessionContext);
    }

    public function outputAttrs()
    {
        $result = parent::outputAttrs();
        $rec = $this->fetchData();
        $result['record'] = $rec;
        $result['record_name'] = $this->dataRecordName;
        return $result;
    }

}
