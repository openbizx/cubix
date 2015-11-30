<?php

use Openbizx\Openbizx;
use Openbizx\I18n\I18n;
use Openbizx\Data\DataRecord;

require_once "SettingForm.php";

class SettingTranslateForm extends SettingForm
{

    protected $translateDO = "repository.setting.do.SettingTranslateDO";

    public function fetchData()
    {

        $this->activeRecord = null;
        $resultRaw = parent::fetchData();
        foreach ($resultRaw as $key => $value) {
            if (substr($key, 0, 1) == '_') {
                $key = substr($key, 1);
            }
            $result[$key] = $value;
        }

        $lang = Openbizx::$app->getClientProxy()->getFormInputs("fld_lang");
        $lang ? $lang : $lang = I18n::getCurrentLangCode();
        $record_id = $result["Id"];

        $transDO = Openbizx::getObject($this->translateDO, 1);
        $currentRecord = $transDO->fetchOne("[lang]='$lang'");
        if ($currentRecord) {
            $currentRecord = $currentRecord->toArray();
            foreach ($currentRecord as $field => $value) {
                $result['_' . $field] = $value;
            }
        } else {
            $result['_repo_name'] = "";
            $result['_repo_desc'] = "";
        }
        return $result;
    }

    public function updateRecord()
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

            if ($this->_doUpdate($recArr, $currentRec) == false)
                return;
        }

        $this->notices[] = $this->getMessage("TRANS_SAVED_MSG", $recArr['lang']);
        $this->rerender();
    }

    protected function _doUpdate($inputRecord, $currentRecord)
    {

        $lang = $inputRecord['lang'];
        $record_id = $currentRecord["Id"];
        $transDO = Openbizx::getObject($this->translateDO, 1);

        $newRecord = array(
            "lang" => $lang,
        );
        foreach ($inputRecord as $field => $value) {
            if (substr($field, 0, 1) == '_') {
                $newRecord[substr($field, 1, strlen($field) - 1)] = $value;
            }
        }

        $searchRule = "[lang]='$lang'";
        $currentRecord = $transDO->fetchOne($searchRule);
        if ($currentRecord) {
            $currentRecord = $currentRecord->toArray();
        }


        $dataRec = new DataRecord($currentRecord, $transDO);

        foreach ($newRecord as $k => $v) {
            $dataRec[$k] = $v; // or $dataRec->$k = $v;
        }
        try {
            //test dump data
            //var_dump($currentRecord);
            //var_dump($dataRec->toArray());exit;
            $dataRec->save();
        } catch (Openbizx\Validation\Exception $e) {
            $errElements = $this->getErrorElements($e->errors);
            if (count($e->errors) == count($errElements)) {
                $this->processFormObjError($errElements);
            } else {
                $errmsg = implode("<br />", $e->errors);
                Openbizx::$app->getClientProxy()->showErrorMessage($errmsg);
            }
            return false;
        } catch (Openbizx\data\Exception $e) {
            $this->processDataException($e);
            return false;
        }
        $this->activeRecord = null;
        $this->getActiveRecord($dataRec["Id"]);

        $this->runEventLog();
        return true;
    }

}
