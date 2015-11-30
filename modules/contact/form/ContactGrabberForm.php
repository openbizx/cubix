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
 * @version   $Id: ContactGrabberForm.php 3356 2012-05-31 05:47:51Z rockyswen@gmail.com $
 */
use Openbizx\Openbizx;
use Openbizx\Easy\EasyForm;

class ContactGrabberForm extends EasyForm
{

    public function fetchContact()
    {
        $recArr = $this->readInputRecord();
        $this->setActiveRecord($recArr);
        if (count($recArr) == 0) {
            return;
        }

        try {
            $this->ValidateForm();
        } catch (Openbizx\Validation\Exception $e) {
            $this->processFormObjError($e->errors);
            return;
        }

        $provider = $recArr['provider'];
        $username = $recArr['username'];
        $password = $recArr['password'];
        $credential = array(
            "username" => $username,
            "password" => $password
        );

        $contactSvc = Openbizx::getObject("contact.lib.ContactGrabberService");
        try {
            if (!$contactSvc->ValidateCredential($recArr, $provider)) {
                $credential_invaild = Openbizx::getService($provider)->getValidateError();
                $this->processFormObjError($credential_invaild);
                return;
            }
        } catch (Exception $e) {
            $credential_invaild = Openbizx::getService($provider)->getValidateError();
            $this->processFormObjError($credential_invaild);
            return;
        }
        $contacts = $contactSvc->fetchContacts($credential, $provider);
        //save contacts to import db
        $contactImportDO = Openbizx::getObject("contact.do.ContactImportDO");
        $user_id = Openbizx::$app->getUserProfile("Id");

        $contactImportDO->deleteRecords("[user_id]='$user_id'");
        foreach ($contacts as $contactRec) {
            $contactRec['user_id'] = $user_id;
            $contactImportDO->insertRecord($contactRec);
        }
        $this->switchForm("contact.form.ContactGrabberListForm");
    }

}

