<?php
/**
 * Openbizx Cubi Application Platform
 *
 * LICENSE http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 *
 * @package   cubi.common.form
 * @copyright Copyright (c) 2005-2011, Openbiz Technology LLC
 * @license   http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 * @link      http://code.google.com/p/openbiz-cubi/
 * @version   $Id: GeneralSettingForm.php 3355 2012-05-31 05:43:33Z rockyswen@gmail.com $
 */

use Openbizx\Data\Helpers\QueryStringParam;
use Openbizx\Easy\EasyForm;

class GeneralSettingForm extends EasyForm
{        
    public function fetchData(){
        if ($this->activeRecord != null)
            return $this->activeRecord;
        
        $dataObj = $this->getDataObj();
        if ($dataObj == null) return;
		
    	QueryStringParam::setBindValues($this->searchRuleBindValues);        
        	
        if ($this->isRefreshData)   $dataObj->resetRules();
        else $dataObj->clearSearchRule();

        if ($this->fixSearchRule)
        {
            if ($this->searchRule)
                $searchRule = $this->searchRule . " AND " . $this->fixSearchRule;
            else
                $searchRule = $this->fixSearchRule;
        }

        $dataObj->setSearchRule($searchRule);
        QueryStringParam::setBindValues($this->searchRuleBindValues);        

        $resultRecords = $dataObj->fetch();
        foreach($resultRecords as $record){
        	$settingRecord["_".$record['name']] = $record["value"];
        }
        
        $this->recordId = $resultRecords[0]['Id'];
        $this->setActiveRecord($settingRecord);

        QueryStringParam::ReSet();
        return $settingRecord;    
    }
    
    public function updateRecord()
    {
        $currentRec = $this->fetchData();
        $recArr = $this->readInputRecord();

        if (count($recArr) == 0)
            return;

        try
        {
            $this->ValidateForm();
        }
        catch (Openbizx\Validation\Exception $e)
        {
            $this->processFormObjError($e->errors);
            return;
        }
		
        // new save logic
        $settingDo = $this->getDataObj();
        
        foreach ($this->dataPanel as $element)
        {
            $value = $recArr[$element->fieldName];
            if ($value === null){ 
            	continue;
            } 
            if(substr($element->fieldName,0,1)=='_'){
	            $name = substr($element->fieldName,1);
            	$recArrParam = array(
            		"name"	  => $name,
            		"value"   => $value,
	            	"section" => $element->elementSet,
	            	"type" 	  => $element->className,	            
	            );
	            //check if its exsit
	            $record = $settingDo->fetchOne("[name]='$name'");
	            if($record){
	            	//update it
	            	$recArrParam["Id"] = $record->Id;
	            	$settingDo->updateRecord($recArrParam,$record->toArray());
	            }else{
	            	//insert it	            	
	            	$settingDo->insertRecord($recArrParam);
	            }
            }
        }
        
               
        // in case of popup form, close it, then rerender the parent form
        if ($this->parentFormName)
        {
            $this->close();

            $this->renderParent();
        }

        $this->processPostAction();

    }


}  
