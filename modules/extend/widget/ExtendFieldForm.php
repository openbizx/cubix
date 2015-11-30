<?php

/**
 * Openbizx Cubi Application Platform
 *
 * LICENSE http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 *
 * @package   cubi.extend.widget
 * @copyright Copyright (c) 2005-2011, Openbiz Technology LLC
 * @license   http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 * @link      http://code.google.com/p/openbiz-cubi/
 * @version   $Id: ExtendFieldForm.php 3360 2012-05-31 06:00:17Z rockyswen@gmail.com $
 */
use Openbizx\Openbizx;
use Openbizx\Easy\PickerForm;

class ExtendFieldForm extends PickerForm
{

    protected $settingOptionDO = "extend.do.ExtendSettingOptionDO";

    protected function _doUpdate($inputRecord, $currentRecord)
    {
        $this->processOptions($inputRecord['options'], $currentRecord['Id']);
        return parent::_doUpdate($inputRecord, $currentRecord);
    }

    public function insertToParent()
    {

        $recArr = $this->readInputRecord();
        $this->setActiveRecord($recArr);
        if (count($recArr) == 0)
            return;

        try {
            $this->ValidateForm();
        } catch (Openbizx\Validation\Exception $e) {
            $this->processFormObjError($e->errors);
            return;
        }


        if (!$this->parentFormElemName) {
            //its only supports 1-m assoc now	        	        
            $parentForm = Openbizx::getObject($this->parentFormName);
            //$parentForm->getDataObj()->clearSearchRule();
            $parentDo = $parentForm->getDataObj();

            $column = $parentDo->association['Column'];
            $field = $parentDo->getFieldNameByColumn($column);
            $parentRefVal = $parentDo->association["FieldRefVal"];

            $recArr[$field] = $parentRefVal;
            if ($parentDo->association['Relationship'] == '1-M') {
                $cond_column = $parentDo->association['CondColumn'];
                $cond_value = $parentDo->association['CondValue'];
                if ($cond_column) {
                    $cond_field = $parentDo->getFieldNameByColumn($cond_column);
                    $recArr[$cond_field] = $cond_value;
                }
                $recId = $parentDo->InsertRecord($recArr);
            } else {
                $recId = $this->getDataObj()->InsertRecord($recArr);
                $this->addToParent($recId);
            }

            $this->processOptions($recArr['options'], $recId);
        }

        if ($this->parentFormElemName && $this->pickerMap) {
            return; //not supported yet
        }


        $selIds[] = $recId;

        $this->close();
        if ($parentForm->parentFormName) {
            $parentParentForm = Openbizx::getObject($parentForm->parentFormName);
            $parentParentForm->rerender();
        } else {
            $parentForm->rerender();
        }
        return $recordId;
    }

    public function processOptions($option_str, $setting_id, $lang = null)
    {
        $optDO = Openbizx::getObject($this->settingOptionDO);
        $optionArr = explode(";", $option_str);
        $i = 1;
        $setting_id = (int) $setting_id;
        $optDO->deleteRecords("[setting_id]='$setting_id' AND lang='$lang'");
        foreach ($optionArr as $option) {
            $optRec = array(
                "setting_id" => (int) $setting_id,
                "lang" => $lang,
                "text" => $option,
                "value" => $i
            );
            $optDO->insertRecord($optRec);
            $i++;
        }
    }

}
