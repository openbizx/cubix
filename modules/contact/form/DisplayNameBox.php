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
 * @version   $Id: DisplayNameBox.php 3356 2012-05-31 05:47:51Z rockyswen@gmail.com $
 */
use Openbizx\Openbizx;
use Openbizx\Easy\Element\Listbox;

class DisplayNameBox extends Listbox
{

    public function getValue()
    {
        $value = parent::getValue();
        $firstname = Openbizx::$app->getClientProxy()->getFormInputs("fld_first_name");
        $lastname = Openbizx::$app->getClientProxy()->getFormInputs("fld_last_name");
        $company = Openbizx::$app->getClientProxy()->getFormInputs("fld_company");
        $value = str_replace("@@Firstname@@", $firstname, $value);
        $value = str_replace("@@Lastname@@", $lastname, $value);
        $value = str_replace("@@Company@@", $company, $value);
        return $value;
    }

    public function translateValue($value)
    {
        if (strtoupper($this->getFormObj()->formType) != 'NEW') {
            $rec = $this->getFormObj()->getActiveRecord();
        }
        $firstname = $rec['first_name'];
        $lastname = $rec['last_name'];
        $company = $rec['company'];
        $value = str_replace("@@Firstname@@", $firstname, $value);
        $value = str_replace("@@Lastname@@", $lastname, $value);
        $value = str_replace("@@Company@@", $company, $value);
        return $value;
    }

    public function render()
    {
        $fromList = array();
        $this->getFromList($fromList);
        $value = $this->value ? $this->value : $this->getDefaultValue();
        $disabledStr = ($this->getEnabled() == "N") ? "DISABLED=\"true\"" : "";
        $style = $this->getStyle();
        $func = $this->getFunction();

        //$sHTML = "<SELECT NAME=\"" . $this->objectName . "[]\" ID=\"" . $this->objectName ."\" $disabledStr $this->htmlAttr $style $func>";
        $sHTML = "<SELECT NAME=\"" . $this->objectName . "\" ID=\"" . $this->objectName . "\" $disabledStr $this->htmlAttr $style $func>";

        if ($this->blankOption) { // ADD a blank option
            $entry = explode(",", $this->blankOption);
            $text = $entry[0];
            $value = ($entry[1] != "") ? $entry[1] : null;
            $entryList = array(array("val" => $value, "txt" => $text));
            $fromList = array_merge($entryList, $fromList);
        }

        $defaultValue = null;
        foreach ($fromList as $option) {
            $optionTranslated = $this->translateValue($option['val']);
            if ($optionTranslated != $value) {
                $selectedStr = '';
            } else {
                $selectedStr = "SELECTED";
                $defaultValue = $option['val'];
            }
            $sHTML .= "<OPTION VALUE=\"" . $option['val'] . "\" $selectedStr>" . $option['txt'] . "</OPTION>";
        }
        if ($defaultValue == null) {
            $defaultOpt = array_shift($fromList);
            $defaultValue = $defaultOpt['val'];
            array_unshift($fromList, $defaultOpt);
        }


        $this->setValue($defaultValue);
        $sHTML .= "</SELECT>";
        return $sHTML;
    }

}

