<?php

use Openbizx\Openbizx;
use Openbizx\Data\Helpers\QueryStringParam;
use Openbizx\Easy\EasyForm;

require_once dirname(__FILE__) . "/UserForm.php";

class InitializeForm extends EasyForm
{

    public function SystemInit()
    {
        $currentRec = $this->fetchData();
        $recArr = $this->readInputRecord();

        if (count($recArr) == 0) {
            return;
        }

        try {
            $this->ValidateForm();
        } catch (Openbizx\Validation\Exception $e) {
            $this->processFormObjError($e->errors);
            return;
        }

        // new save logic
        $user_id = 0;
        $prefDo = $this->getDataObj();

        foreach ($this->dataPanel as $element) {
            $value = $recArr[$element->fieldName];
            if ($value === null) {
                continue;
            }
            if ($element->fieldName == 'password') {
                //update admin password
                $currentUserId = Openbizx::$app->getUserProfile("Id");
                $userRec = Openbizx::getObject("system.do.UserSystemDO")->fetchById($currentUserId);
                $userRec['password'] = hash(HASH_ALG, $value);
                $userRec->save();
                continue;
            }
            if (substr($element->fieldName, 0, 1) == '_') {
                $name = substr($element->fieldName, 1);
                $recArrParam = array(
                    "user_id" => $user_id,
                    "name" => $name,
                    "value" => $value,
                    "section" => $element->elementSetCode,
                    "type" => $element->className,
                );
                //check if its exsit
                $record = $prefDo->fetchOne("[user_id]='$user_id' and [name]='$name'");
                if ($record) {
                    //update it
                    $recArrParam["Id"] = $record->Id;
                    $prefDo->updateRecord($recArrParam, $record->toArray());
                } else {
                    //insert it	            	
                    $prefDo->insertRecord($recArrParam);
                }

                //update default app_init setting
                $config_file = OPENBIZ_APP_PATH . '/bin/app_init.php';
                switch ($name) {
                    case "system_name":
                        if ($value != OPENBIZ_DEFAULT_SYSTEM_NAME) {
                            //update default theme CUBI_DEFAULT_THEME_NAME
                            $data = file_get_contents($config_file);
                            $data = preg_replace("/define\([\'\\\"]{1}OPENBIZ_DEFAULT_SYSTEM_NAME[\'\\\"]{1}.*?\)\;/i", "define('OPENBIZ_DEFAULT_SYSTEM_NAME','$value');", $data);
                            @file_put_contents($config_file, $data);
                        }
                        break;
                    case "siteurl":
                        //update default theme SITE_URL
                        $data = file_get_contents($config_file);
                        $data = preg_replace("/define\([\'\\\"]{1}SITE_URL[\'\\\"]{1}.*?\)\;/i", "define('SITE_URL','$value');", $data);
                        @file_put_contents($config_file, $data);
                        break;
                    case "sessionstrict":
                        //update default theme CUBI_SESSION_STRICT
                        if ($value != CUBI_SESSION_STRICT) {
                            $data = file_get_contents($config_file);
                            $data = preg_replace("/define\([\'\\\"]{1}CUBI_SESSION_STRICT[\'\\\"]{1}.*?\)\;/i", "define('CUBI_SESSION_STRICT','$value');", $data);
                            @file_put_contents($config_file, $data);
                        }
                        break;

                    case "language":
                        if ($value != OPENBIZ_DEFAULT_LANGUAGE) {
                            //update default theme OPENBIZ_DEFAULT_LANGUAGE
                            $data = file_get_contents($config_file);
                            $data = preg_replace("/define\([\'\\\"]{1}OPENBIZ_DEFAULT_LANGUAGE[\'\\\"]{1}.*?\)\;/i", "define('OPENBIZ_DEFAULT_LANGUAGE','$value');", $data);
                            @file_put_contents($config_file, $data);

                            //make changes now
                            Openbizx::$app->getSessionContext()->setVar("LANG", $value);
                        }
                        break;
                }
            }
        }
        //set initialized.lock 
        $initLock = OPENBIZ_APP_PATH . '/files/initialize.lock';
        $data = '1';
        file_put_contents($initLock, $data);

        $this->processPostAction();
    }

    public function allowAccess($access = null)
    {
        $initLock = OPENBIZ_APP_PATH . '/files/initialize.lock';
        if (is_file($initLock)) {
            $pageURL = OPENBIZ_APP_INDEX_URL . "/system/general_default";
            Openbizx::$app->getClientProxy()->redirectPage($pageURL);
            return;
        }
        return parent::allowAccess($access);
    }

    public function fetchData()
    {
        if ($this->activeRecord != null)
            return $this->activeRecord;

        $dataObj = $this->getDataObj();
        if ($dataObj == null)
            return;

        $this->fixSearchRule = "[user_id]='0'";

        if (!$this->fixSearchRule && !$this->searchRule)
            return array();

        QueryStringParam::setBindValues($this->searchRuleBindValues);


        if ($this->isRefreshData)
            $dataObj->resetRules();
        else
            $dataObj->clearSearchRule();

        if ($this->fixSearchRule) {
            if ($this->searchRule)
                $searchRule = $this->searchRule . " AND " . $this->fixSearchRule;
            else
                $searchRule = $this->fixSearchRule;
        }

        $dataObj->setSearchRule($searchRule);
        QueryStringParam::setBindValues($this->searchRuleBindValues);

        $resultRecords = $dataObj->fetch();
        foreach ($resultRecords as $record) {
            $prefRecord["_" . $record['name']] = $record["value"];
        }
        $prefRecord["_siteurl"] = SITE_URL;
        $prefRecord["_system_name"] = OPENBIZ_DEFAULT_SYSTEM_NAME;

        $this->recordId = $resultRecords[0]['Id'];
        $this->setActiveRecord($prefRecord);

        QueryStringParam::ReSet();
        return $prefRecord;
    }

}
