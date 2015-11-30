<?php

use Openbizx\Openbizx;
use Openbizx\Easy\EasyForm;

class InitializeGroupForm extends EasyForm
{

    protected $groupDO = "system.do.GroupDO";

    public function initialize()
    {
        $currentRec = $this->fetchData();
        $recArr = $this->readInputRecord();
        $this->setActiveRecord($recArr);
        if (count($recArr) != 0) {

            try {
                $this->ValidateForm();
            } catch (Openbizx\Validation\Exception $e) {
                $this->processFormObjError($e->errors);
                return;
            }

            $groupDO = Openbizx::getObject($this->groupDO);
            //rename default group
            if ((int) $recArr['rename_default_group'] == 1) {
                $defaultGroupRec = $groupDO->fetchOne("", "[Id] ASC");
                $defaultGroupRec['name'] = $recArr['rename_default_group_name'];
                $defaultGroupRec->save();
            }

            //add new groups	        
            foreach (array(
        "add_group_1",
        "add_group_2",
        "add_group_3",
        "add_group_4",
        "add_group_5"
            ) as $addGroup) {
                if ((int) $recArr[$addGroup] == 1) {
                    $groupRec = array(
                        "name" => $recArr[$addGroup . '_name'],
                        "status" => 1,
                    );
                    $groupDO->insertRecord($groupRec);
                }
            }

            //default data sharing setting
            $prefDo = Openbizx::getObject("myaccount.do.PreferenceDO");
            $config_file = OPENBIZ_APP_PATH . '/bin/app_init.php';
            $value = $recArr['_data_acl'];
            //update default theme CUBI_DATA_ACL
            if ($value != CUBI_DATA_ACL) {
                $data = file_get_contents($config_file);
                $data = preg_replace("/define\([\'\\\"]{1}CUBI_DATA_ACL[\'\\\"]{1}.*?\)\;/i", "define('CUBI_DATA_ACL','$value');", $data);
                @file_put_contents($config_file, $data);
            }
            $recArrParam = array(
                "user_id" => 0,
                "name" => 'data_acl',
                "value" => $value,
                "section" => 'General',
                "type" => 'DropDownList',
            );
            //check if its exsit
            $record = $prefDo->fetchOne("[user_id]='0' and [name]='data_acl'");
            if ($record) {
                //update it
                $recArrParam["Id"] = $record->Id;
                $prefDo->updateRecord($recArrParam, $record->toArray());
            } else {
                //insert it	            	
                $prefDo->insertRecord($recArrParam);
            }


            $value = $recArr['_group_data_share'];
            if ($value != CUBI_GROUP_DATA_SHARE) {
                $data = file_get_contents($config_file);
                $data = preg_replace("/define\([\'\\\"]{1}CUBI_GROUP_DATA_SHARE[\'\\\"]{1}.*?\)\;/i", "define('CUBI_GROUP_DATA_SHARE','$value');", $data);
                @file_put_contents($config_file, $data);
            }
            $recArrParam = array(
                "user_id" => 0,
                "name" => 'group_data_share',
                "value" => $value,
                "section" => 'General',
                "type" => 'DropDownList',
            );
            //check if its exsit
            $record = $prefDo->fetchOne("[user_id]='0' and [name]='group_data_share'");
            if ($record) {
                //update it
                $recArrParam["Id"] = $record->Id;
                $prefDo->updateRecord($recArrParam, $record->toArray());
            } else {
                //insert it	            	
                $prefDo->insertRecord($recArrParam);
            }


            //put init lock
            $group_init_lock = OPENBIZ_APP_FILE_PATH . DIRECTORY_SEPARATOR . 'initialize_group.lock';
            file_put_contents($group_init_lock, '1');

            //redirect back to last view
            $lastViewURL = $this->getWebpageObject()->getLastViewURL();
            Openbizx::$app->getClientProxy()->redirectPage($lastViewURL);
            return;
        }
    }

}
