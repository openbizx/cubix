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
 * @version   $Id: ContactGrabberImportForm.php 3356 2012-05-31 05:47:51Z rockyswen@gmail.com $
 */
use Openbizx\Openbizx;
use Openbizx\Easy\EasyForm;

class ContactGrabberImportForm extends EasyForm
{

    public $SelectedContacts = 0;

    public function fetchData()
    {
        $result = parent::fetchData();
        $user_id = Openbizx::$app->getUserProfile("Id");
        $rs = $this->getDataObj()->directFetch("[selected]=1 and [user_id]='$user_id'");
        $this->SelectedContacts = $rs->count();
        return $result;
    }

    public function import()
    {

        $recArr = $this->readInputRecord();
        if (count($recArr) == 0) {
            return;
        }

        $ImportOpt = array();

        $user_id = Openbizx::$app->getUserProfile("Id");
        $permOpt['group_id'] = Openbizx::$app->getUserProfile("default_group");
        $permOpt['group_perm'] = $recArr['group_perm'];
        $permOpt['other_perm'] = $recArr['other_perm'];

        switch ($recArr['type_selector']) {
            case '0': //assign a exsits
                $ImportOpt['contact_type'] = $recArr['contact_type_exist'];
                break;
            case '1': //create new type
                $new_type_name = $recArr['contact_type_new'];
                if ($new_type_name == '') {
                    $element = $this->dataPanel->get('fld_type_new');
                    if ($element->label) {
                        $elementName = $element->label;
                    } else {
                        $elementName = $element->text;
                    }
                    $errorMessage = $this->getMessage("FORM_ELEMENT_REQUIRED", array($elementName));
                    $this->validateErrors[$element->objectName] = $errorMessage;
                    $this->processFormObjError($this->validateErrors);
                    return false;
                }
                //create a new type with specfied sharing setting
                $newTypeRec = $permOpt;
                $newTypeRec['name'] = $new_type_name;
                $newTypeRec['published'] = 1;
                $newTypeRec['sortorder'] = 50;
                $contactTypeDO = Openbizx::getObject("contact.do.ContactTypeDO");
                $type_id = $contactTypeDO->insertRecord($newTypeRec);
                $ImportOpt['contact_type'] = $type_id;
                break;
        }

        //start import contact data
        $contactDO = Openbizx::getObject("contact.do.ContactDO");
        $contactImportDO = Openbizx::getObject("contact.do.ContactImportDO");
        $selectedContactRecs = $contactImportDO->directFetch("[selected]='1'");
        foreach ($selectedContactRecs as $contactRec) {
            $newContactRec = $contactRec;
            $newContactRec['group_id'] = $permOpt['group_id'];
            $newContactRec['group_perm'] = $permOpt['group_perm'];
            $newContactRec['other_perm'] = $permOpt['other_perm'];
            $newContactRec['type_id'] = $ImportOpt['contact_type'];
            $newContactRec['sortorder'] = '50';
            unset($newContactRec['user_id']);
            if (!$newContactRec['company']) {
                $newContactRec['company'] = 'N/A';
            }

            //check exsits
            $foreign_key = $newContactRec['foreign_key'];
            $source = $newContactRec['source'];

            $recs = $contactDO->directfetch("[foreign_key]='$foreign_key'
        										AND [source]='$source'
        										AND [create_by]='$user_id'");

            if ($recs->count() == 0) {
                $contactDO->insertRecord($newContactRec);
            }
        }

        if ($this->parentFormName) {
            $this->close();
            $this->renderParent();
        }

        $this->processPostAction();
    }

}

