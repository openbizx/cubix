<?php

/**
 * Openbizx Cubi Application Platform
 *
 * LICENSE http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 *
 * @package   cubi.email.form
 * @copyright Copyright (c) 2005-2011, Openbiz Technology LLC
 * @license   http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 * @link      http://code.google.com/p/openbiz-cubi/
 * @version   $Id: EmailQueueForm.php 3358 2012-05-31 05:57:58Z rockyswen@gmail.com $
 */
use Openbizx\Openbizx;
use Openbizx\Easy\EasyForm;

class EmailQueueForm extends EasyForm
{

    public function sendAllPendingEmails()
    {
//       if ($this->resource != "" && !$this->allowAccess($this->resource.".sendemail"))
//            return Openbizx::$app->getClientProxy()->redirectView(OPENBIZ_ACCESS_DENIED_VIEW);		

        $emailSvc = Openbizx::getService(CUBI_USER_EMAIL_SERVICE);
        $emailSvc->sendEmailFromQueue();
        $this->runEventLog();
        $this->rerender();
        return true;
    }

    public function sendEmails()
    {
//       if ($this->resource != "" && !$this->allowAccess($this->resource.".sendemail"))
//            return Openbizx::$app->getClientProxy()->redirectView(OPENBIZ_ACCESS_DENIED_VIEW);

        if ($id == null || $id == '')
            $id = Openbizx::$app->getClientProxy()->getFormInputs('_selectedId');

        $selIds = Openbizx::$app->getClientProxy()->getFormInputs('row_selections', false);
        if ($selIds == null)
            $selIds[] = $id;

        $emailSvc = Openbizx::getService(CUBI_USER_EMAIL_SERVICE);

        foreach ($selIds as $id) {
            try {
                $emailSvc->sendEmailNow($id);
            } catch (Openbizx\data\Exception $e) {
                $this->processDataException($e);
                return;
            }
        }
        if (strtoupper($this->formType) == "LIST")
            $this->rerender();

        $this->runEventLog();
        $this->processPostAction();
        return true;
    }

    public function deleteAllEmails()
    {
        if ($this->resource != "" && !$this->allowAccess($this->resource . ".delete"))
            return Openbizx::$app->getClientProxy()->redirectView(OPENBIZ_ACCESS_DENIED_VIEW);

        try {
            $this->getDataObj()->deleteRecords();
        } catch (Openbizx\data\Exception $e) {
            $this->processDataException($e);
            return;
        }

        if (strtoupper($this->formType) == "LIST")
            $this->rerender();

        $this->runEventLog();
        $this->processPostAction();
        return true;
    }

    public function deleteSentEmails()
    {
        if ($this->resource != "" && !$this->allowAccess($this->resource . ".delete"))
            return Openbizx::$app->getClientProxy()->redirectView(OPENBIZ_ACCESS_DENIED_VIEW);

        try {
            $this->getDataObj()->deleteRecords("[status]='sent'");
        } catch (Openbizx\data\Exception $e) {
            $this->processDataException($e);
            return;
        }

        if (strtoupper($this->formType) == "LIST")
            $this->rerender();

        $this->runEventLog();
        $this->processPostAction();
        return true;
    }

    public function fetchDataSet()
    {
        $resultRecords = parent::fetchDataSet()->toArray();
        $emailSvc = Openbizx::getService(EMAIL_SERVICE);
        for ($i = 0; $i < count($resultRecords); $i++) {

            $account = $emailSvc->accounts->get($resultRecords[$i]['sender']);
            $resultRecords[$i]['sender'] = $account->fromName;
            $resultRecords[$i]['sender_mail'] = $account->fromEmail;
            $resultRecords[$i]['recipient_name'] = $resultRecords[$i]['recipient'];
            $resultRecords[$i]['recipient_email'] = $resultRecords[$i]['recipient'];
        }
        return $resultRecords;
    }

    public function fetchData()
    {
        $resultRecords = parent::fetchData();
        $emailSvc = Openbizx::getService(EMAIL_SERVICE);

        $account = $emailSvc->accounts->get($resultRecords['sender']);
        $resultRecords['sender'] = $account->fromName;
        $resultRecords['sender_mail'] = $account->fromEmail;
        $resultRecords['recipient_name'] = $resultRecords['recipient'];
        $resultRecords['recipient_email'] = $resultRecords['recipient'];
        //$resultRecords['sender'] = "<a href=\"mailto:".$account->fromEmail."\" >".$account->fromName."</a> &lt;".$account->fromEmail."&gt;   ";
        //$resultRecords['recipient_name'] = "<a href=\"mailto:".$resultRecords['recipient']."\" >".$resultRecords['recipient_name']."</a> &lt;".$resultRecords['recipient']."&gt;   ";

        return $resultRecords;
    }

}

