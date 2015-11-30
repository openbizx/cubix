<?php

/**
 * Openbizx Cubi Application Platform
 *
 * LICENSE http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 *
 * @package   cubi.location.widget
 * @copyright Copyright (c) 2005-2011, Openbiz Technology LLC
 * @license   http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 * @link      http://code.google.com/p/openbiz-cubi/
 * @version   $Id: LocationForm.php 4224 2012-09-16 14:16:02Z rockyswen@gmail.com $
 */
use Openbizx\Openbizx;
use Openbizx\Easy\EasyForm;

class LocationForm extends EasyForm
{

    protected $geocode_url = "http://maps.googleapis.com/maps/api/geocode/json?sensor=false";

    // keep canUpdate in session
    public function loadStatefullVars($sessionContext)
    {
        parent::loadStatefullVars($sessionContext);
        $sessionContext->loadObjVar($this->objectName, "CanUpdateRecord", $this->canUpdateRecord);
    }

    public function saveStatefullVars($sessionContext)
    {
        parent::saveStatefullVars($sessionContext);
        $sessionContext->saveObjVar($this->objectName, "CanUpdateRecord", $this->canUpdateRecord);
    }

    public function close()
    {
        return parent::close();
    }

    public function InsertRecord()
    {
        parent::InsertRecord();
        $this->close();
    }

    public function UpdateRecord()
    {
        parent::UpdateRecord();
        $this->close();
    }

    protected function readInputRecord()
    {
        $recArr = parent::readInputRecord();
        $loc = $this->getLatLong($recArr['address']);
        if ($loc) {
            $recArr['latitude'] = $loc['lat'];
            $recArr['longtitude'] = $loc['lng'];
        }
        return $recArr;
    }

    protected function getLatLong($address)
    {
        require_once 'Zend/Json.php';
        $jsonValue = file_get_contents($this->geocode_url . "&address=" . urlencode($address));
        $jsonArray = \Zend_Json::decode($jsonValue, true);
        if ($jsonArray['status'] != 'OK') {
            $errorMessage = "Invalid address"; //$this->getMessage("FORM_ELEMENT_REQUIRED",array($elementName));
            $this->validateErrors['fld_address'] = $errorMessage;
            return null;
        }
        $location = $jsonArray['results'][0]['geometry']['location'];
        return $location;
    }

    protected function validateForm($cleanError = true)
    {
        if (count($this->validateErrors) > 0) {
            throw new Openbizx\Validation\Exception($this->validateErrors);
            return false;
        }
        parent::validateForm($cleanError);
    }

    public function deleteLocation($id)
    {
        parent::deleteRecord($id);
        $parentForm = Openbizx::getObject($this->parentFormName);
        $parentForm->rerender();
        return parent::close();
    }

    public function UpdateLocation($id, $lat, $lng)
    {
        $location = $this->getDataObj()->fetchById($id);
        $location['latitude'] = $lat;
        $location['longtitude'] = $lng;
        $location->save();
        return;
    }

    public function addLocation()
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
            $cond_column = $parentDo->association['CondColumn'];
            $cond_value = $parentDo->association['CondValue'];
            if ($cond_column) {
                $cond_field = $parentDo->getFieldNameByColumn($cond_column);
                $recArr[$cond_field] = $cond_value;
            }
        }

        if ($this->parentFormElemName && $this->pickerMap) {
            return; //not supported yet
        }
        $recId = $parentDo->InsertRecord($recArr);

        $parentForm = Openbizx::getObject($this->parentFormName);
        $parentForm->rerender();
        return parent::close();
    }

}

