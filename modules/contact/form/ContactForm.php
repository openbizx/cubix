<?php

/**
 * Openbizx Cubi Application Platform
 *
 * LICENSE http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 *
 * @package   cubi.contact.form
 * @copyright Copyright (c) 2005-2011, Openbiz Technology LLC
 * @license   http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 * @link      http://code.google.com/p/openbiz-cubi/
 * @version   $Id: ContactForm.php 5008 2012-12-31 14:55:34Z hellojixian@gmail.com $
 */
use Openbizx\Openbizx;

include_once Openbizx::$app->getModulePath() . '/changelog/form/ChangeLogNoCommentForm.php';

class ContactForm extends ChangeLogForm
{

    public function insertRecord()
    {
        $recArr = $this->readInputRecord();
        $this->setActiveRecord($recArr);
        if (count($recArr) == 0)
            return;

        //generate fast_index
        $svcobj = Openbizx::getService("service.chineseService");
        if ($svcobj->isChinese($recArr['display_name'])) {
            $fast_index = $svcobj->Chinese2Pinyin($recArr['display_name']);
        } else {
            $fast_index = $recArr['display_name'];
        }
        $recArr['fast_index'] = substr($fast_index, 0, 1);

        try {
            $this->ValidateForm();
        } catch (Openbizx\Validation\Exception $e) {
            $this->processFormObjError($e->errors);
            return;
        }

        $this->_doInsert($recArr);

        $this->commitFormElements(); // commit change in FormElement
        // in case of popup form, close it, then rerender the parent form
        if ($this->parentFormName) {
            $this->close();

            $this->renderParent();
        }

        $this->processPostAction();
    }

    public function quickSearch($start = null, $end = null)
    {
        $start = strtoupper($start);
        $end = strtoupper($end);
        $searchRule = "";
        if ($start != '' && $end != '') {
            $searchRule = "'$start'<=[fast_index] AND [fast_index]<='$end'";
        } elseif ($start) {
            $searchRule = "'$start'<[fast_index]";
        } else {
            $searchRule = "";
        }

        $this->setFixSearchRule($searchRule);
        $this->rerender();
    }

    public function updateRecord()
    {
        $currentRec = $this->fetchData();
        $recArr = $this->readInputRecord();
        $this->setActiveRecord($recArr);

        //generate fast_index
        if ($currentRec['display_name'] != $recArr['display_name']) {
            $svcobj = Openbizx::getService("service.chineseService");
            if ($svcobj->isChinese($recArr['display_name'])) {
                $fast_index = $svcobj->Chinese2Pinyin($recArr['display_name']);
            } else {
                $fast_index = $recArr['display_name'];
            }
            $recArr['fast_index'] = substr($fast_index, 0, 1);
        }

        if ($currentRec['user_id'] != 0) {
            $user_email = Openbizx::getObject("system.do.UserDO", 1)->fetchById($currentRec['user_id'])->email;
        }

        if ($user_email != $recArr['email'] && $currentRec['user_id'] != 0 && $recArr['email'] != '' ) {
            //check if email address duplicate
            if ($this->_checkDupEmail($recArr['email'], $currentRec['user_id'])) {
                $this->setActiveRecord($recArr);
                $errorMessage = $this->GetMessage("EMAIL_USED");
                $errors['fld_email'] = $errorMessage;
                $this->processFormObjError($errors);
                return;
            }

            //auto update user's email
            $email = $currentRec['email'];
            $userRec = Openbizx::getObject("system.do.UserDO", 1)->fetchById($currentRec['user_id']);
            $userRec['email'] = $recArr['email'];
            $userRec->save();
        }
        if (count($recArr) != 0) {
            try {
                $this->ValidateForm();
            } catch (Openbizx\Validation\Exception $e) {
                $this->processFormObjError($e->errors);
                return;
            }

            if ($this->_doUpdate($recArr, $currentRec) == false) {
                return;
            }

            $this->commitFormElements(); // commit change in FormElement
        }
        // in case of popup form, close it, then rerender the parent form
        if ($this->parentFormName) {
            $this->close();
            $this->renderParent();
        }

        $this->processPostAction();
    }

    protected function _checkDupEmail($email, $ignored_id = 0)
    {
        $searchTxt = "[email]='$email'";
        // query UserDO by the email
        $userDO = Openbizx::getObject("system.do.UserDO", 1);

        //include optional ID when editing records
        if ($ignored_id > 0) {
            $searchTxt .= " AND [Id]!='$ignored_id'";
        }
        $records = $userDO->directFetch($searchTxt, 1);
        if (count($records) > 0) {
            return true;
        }
        return false;
    }

}
