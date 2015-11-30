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
 * @version   $Id: SmsQueueForm.php 3358 2012-08-15 fsliit@gmail.com $
 */

use Openbizx\Openbizx;
use Openbizx\Easy\EasyForm;

class QueueForm extends EasyForm
{
	public function SendAllPendingSms()
	{
		Openbizx::getService('sms.lib.SmsService')->SendSmsFromQueue();
		if (strtoupper($this->formType) == "LIST")
            $this->rerender();
		$this->runEventLog();
        $this->processPostAction();
		return true;
	}
	
	public function sendSms()
	{
		$Record=$this->getActiveRecord();
		if(is_array($Record) && $Record['status']!='sent')
		{
			$arr[0]=$Record;
			Openbizx::getService('sms.lib.SmsService')->SendSmsFromQueue($arr);
		} 
	 if (strtoupper($this->formType) == "LIST")
            $this->rerender();

        $this->runEventLog();
        $this->processPostAction();
		return true;
	}

	public function DeleteAllSms()
	{
       if ($this->resource != "" && !$this->allowAccess("sms.Manage"))
           return Openbizx::$app->getClientProxy()->redirectView(OPENBIZ_ACCESS_DENIED_VIEW);

        try
        {
          $this->getDataObj()->deleteRecords();
        } 
        catch (Openbizx\data\Exception $e)
        {
           $this->processDataException($e);
           return;
        }
       
        if (strtoupper($this->formType) == "LIST")
            $this->rerender();

        $this->runEventLog();
        $this->processPostAction();
		return true;
	}

	
	public function DeleteSentSms()
	{ 
       if ($this->resource != "" && !$this->allowAccess("sms.Manage"))
            return Openbizx::$app->getClientProxy()->redirectView(OPENBIZ_ACCESS_DENIED_VIEW);

        try
        {
          $this->getDataObj()->deleteRecords("[status]='sent'");
        } 
        catch (Openbizx\data\Exception $e)
        {
           $this->processDataException($e);
           return;
        }
		
       
        if (strtoupper($this->formType) == "LIST")
            $this->rerender();

        $this->runEventLog();
        $this->processPostAction();
		return true;
	}
	
}
