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
 * @version   $Id: EmailLogForm.php 3358 2012-05-31 05:57:58Z rockyswen@gmail.com $
 */

use Openbizx\Openbizx;
use Openbizx\Easy\EasyForm;

class EmailLogForm extends EasyForm
{
	public function fetchDataSet(){
		$resultRecords = parent::fetchDataSet()->toArray();
		$emailSvc = Openbizx::getService(EMAIL_SERVICE);
		for($i=0;$i<count($resultRecords);$i++)
		{
			$account = $emailSvc->accounts->get($resultRecords[$i]['sender']);						
			$resultRecords[$i]['sender_email'] = $resultRecords[$i]['sender'];
			$resultRecords[$i]['sender'] = $resultRecords[$i]['sender_name'];			
			$recipentArr = preg_split('/;/',$resultRecords[$i]['recipients']);
			$resultRecords[$i]['recipients'] = "";
			if(count($recipentArr)>2){
				$spliter=";";
			}
			
			foreach($recipentArr as $recipent){
				preg_match("/(.*?)\<(.*?)\>/si", $recipent, $match);
				if($match[1])
				{
					$resultRecords[$i]['recipients'].=$match[1].$spliter;
					$resultRecords[$i]['recipients_email'].=$match[2].$spliter;
				}
			} 
		}
		
 		return $resultRecords;
	}

	public function fetchData(){
		$resultRecords = parent::fetchData();
		$emailSvc = Openbizx::getService(EMAIL_SERVICE);
		$account = $emailSvc->accounts->get($resultRecords['sender']);						
		
		$resultRecords['sender_email'] = $resultRecords['sender'];
		$resultRecords['sender'] = $resultRecords['sender_name'];
		
		$recipentArr = preg_split('/;/',$resultRecords['recipients']);
		$resultRecords['recipients'] = "";
		if(count($recipentArr)>2){
				$spliter=";";
			}
		foreach($recipentArr as $recipent){
			preg_match("/(.*?)\<(.*?)\>/si", $recipent, $match);
			if($match[1])
			{
				$resultRecords['recipients'].=$match[1].$spliter;
				$resultRecords['recipients_email'].=$match[2].$spliter;
			}
		}
 		return $resultRecords;
	}
	
	public function ExportCSV()
	{
		$excelSvc = Openbizx::getService(EXCEL_SERVICE);	
		$excelSvc->renderCSV($this->objectName);
		$this->runEventLog();
		return true;
	}

    public function ClearLog()	
	{
       if ($this->resource != "" && !$this->allowAccess($this->resource.".delete"))
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
		
}
