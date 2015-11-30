<?php

/**
 * Openbizx Cubi Application Platform
 *
 * LICENSE http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 *
 * @package   cubi.system.form
 * @copyright Copyright (c) 2005-2011, Openbiz Technology LLC
 * @license   http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 * @link      http://code.google.com/p/openbiz-cubi/
 * @version   $Id: UserPreferenceForm.php 3814 2012-08-05 07:27:06Z rockyswen@gmail.com $
 */
/**
 * UserPreferenceForm class - implement the logic of setting user preferences
 *
 * @access public
 */
include_once(Openbizx::$app->getModulePath() . "/system/form/UserPreferenceForm.php");

class SettingForm extends UserPreferenceForm
{

    protected $_userId = null;

    function __construct(&$xmlArr)
    {
        parent::__construct($xmlArr);
        $this->_userId = 0;
    }

    public function allowAccess()
    {
        return parent::allowAccess();
    }

    public function updateRecord()
    {
        $currentRec = $this->fetchData();
        $recArr = $this->readInputRecord();

        if (count($recArr) == 0)
            return;

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
            if (substr($element->fieldName, 0, 1) == '_') {
                $name = substr($element->fieldName, 1);
                $recArrParam = array(
                    "user_id" => $user_id,
                    "name" => $name,
                    "value" => $value,
                    "section" => "SMS",
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
            }
        }
        if ($this->parentFormName) {
            $this->close();

            $this->renderParent();
        }

        $this->processPostAction();
    }

}
