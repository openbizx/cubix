<?php
/**
 * Openbizx Cubi Application Platform
 *
 * LICENSE http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 *
 * @package   cubi.repository.setting.form
 * @copyright Copyright (c) 2005-2011, Openbiz Technology LLC
 * @license   http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 * @link      http://code.google.com/p/openbiz-cubi/
 * @version   $Id: SettingForm.php 3369 2012-05-31 06:13:56Z rockyswen@gmail.com $
 */

/**
 * Openbizx Cubi 
 *
 * LICENSE
 *
 * This source file is subject to the BSD license that is bundled
 * with this package in the file LICENSE.txt.
 *
 * @package   user.form
 * @copyright Copyright (c) 2005-2011, Rocky Swen
 * @license   http://www.opensource.org/licenses/bsd-license.php
 * @link      http://www.phpopenbiz.org/
 * @version   $Id: SettingForm.php 3369 2012-05-31 06:13:56Z rockyswen@gmail.com $
 */

use Openbizx\Openbizx;
use Openbizx\Data\Helpers\QueryStringParam;

/**
 * AccountEditForm class - implement the logic of edit my account form
 *
 * @package user.form
 * @author Rocky Swen
 * @copyright Copyright (c) 2005-2009
 * @access public
 */

use Openbizx\Easy\EasyForm;

class SettingForm extends EasyForm
{
    protected $_userId = null;
    
    function __construct(&$xmlArr)
    {
        parent::__construct($xmlArr);        
        $this->_userId = Openbizx::$app->getUserProfile("Id");
    }
    
    public function allowAccess(){
    	parent::allowAccess();

    	if(Openbizx::$app->getUserProfile("Id"))
    	{
  	 		return 1;
    	}
    	else
    	{
    		return 0;
    	}
    }    
    
    public function fetchData(){
        if ($this->activeRecord != null)
            return $this->activeRecord;
        
        $dataObj = $this->getDataObj();
        if ($dataObj == null) return;

		
        if (!$this->fixSearchRule && !$this->searchRule)
        	return array();
        
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
        	$prefRecord["_".$record['name']] = $record["value"];
        }
        
        $this->recordId = $resultRecords[0]['Id'];
        $this->setActiveRecord($prefRecord);

        QueryStringParam::ReSet();

        return $prefRecord;    
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
        $user_id = 0;
        $prefDo = $this->getDataObj();
        
        foreach ($this->dataPanel as $element)
        {
            $value = $recArr[$element->fieldName];
            if ($value === null){ 
            	continue;
            } 
            if(substr($element->fieldName,0,1)=='_'){
	            $name = substr($element->fieldName,1);
            	$recArrParam = array(
            		"user_id" => $user_id,
            		"name"	  => $name,
            		"value"   => $value,
	            	"section" => $element->elementSetCode,
	            	"type" 	  => $element->className,	            
	            );
	            //check if its exsit
	            $record = $prefDo->fetchOne("[user_id]='$user_id' and [name]='$name'");
	            if($record){
	            	//update it
	            	$recArrParam["Id"] = $record->Id;
	            	$prefDo->updateRecord($recArrParam,$record->toArray());
	            }else{
	            	//insert it	            	
	            	$prefDo->insertRecord($recArrParam);
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
