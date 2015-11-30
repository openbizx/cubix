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
 * @version   $Id: DatabaseForm.php 4550 2012-11-02 06:07:21Z hellojixian@gmail.com $
 */

use Openbizx\Openbizx;
use Openbizx\Object\ObjectFactoryHelper;
use Openbizx\Helpers\TemplateHelper;
use Openbizx\Easy\EasyForm;

class DatabaseForm extends EasyForm
{
	public $configFile;
	public $configNode;
	public $modeStatus;
	
	protected function readMetadata(&$xmlArr)
	{
		parent::readMetaData($xmlArr);
		$this->configFile = isset($xmlArr["EASYFORM"]["ATTRIBUTES"]["CONFIGFILE"]) ? $xmlArr["EASYFORM"]["ATTRIBUTES"]["CONFIGFILE"] : null;
		$this->configNode = isset($xmlArr["EASYFORM"]["ATTRIBUTES"]["CONFIGNODE"]) ? $xmlArr["EASYFORM"]["ATTRIBUTES"]["CONFIGNODE"] : null;
		
	}

	public function getActiveRecord($recId=null)
    {
        if ($this->activeRecord != null)
        {
            if($this->activeRecord['Id'] != null)
            {
                return $this->activeRecord;
            }
        }

        if ($recId==null || $recId=='')
            $recId = Openbizx::$app->getClientProxy()->getFormInputs('_selectedId');
        if ($recId==null || $recId=='')
            return null;
        $this->recordId = $recId;
		$this->fixSearchRule = "[Id]='$recId'";
        $rec=$this->fetchData();
        $this->dataPanel->setRecordArr($rec);
        $this->activeRecord = $rec;
        return $rec;
    }	
	
	public function fetchData(){
		if ($this->activeRecord != null)
            return $this->activeRecord;
            
		if (strtoupper($this->formType) == "NEW")
            return $this->getNewRule();
            
		$file = OPENBIZ_APP_PATH.DIRECTORY_SEPARATOR.$this->configFile;
		if(!is_file($file)){
			return;
		}
		$configArr=ObjectFactoryHelper::getXmlArray($file);
		$nodesArr = $configArr["APPLICATION"][strtoupper($this->configNode)]["DATABASE"];
		$result = array();
		
		preg_match("/\[(.*?)\]=\'(.*?)\'/si",$this->fixSearchRule,$match);
		$name = $match[2];
		
		$recordName = $configArr["APPLICATION"][strtoupper($this->configNode)]["DATABASE"]["ATTRIBUTES"]["NAME"];
		if(!$recordName){
			for($i=0;$i<count($nodesArr);$i++){
				if(is_array($nodesArr[$i]["ATTRIBUTES"])){
					if($nodesArr[$i]["ATTRIBUTES"]["NAME"]==$name){				
						foreach($nodesArr[$i]["ATTRIBUTES"] as $key=>$value){
							$result[$key]=$value;
						}
						$result["Id"]=$nodesArr[$i]["ATTRIBUTES"]["NAME"];
						
					}else{
						continue;
					}
				}
				preg_match("/([0-9]{2})([0-9]{2})\-([0-9]{2})([0-9]{2})/si",$result["EFFECTIVETIME"],$match);
				$result["EFFECTIVETIME_Display"]=$match[1].":".$match[2]." - ".$match[3].":".$match[4];
				$result["starthour"] = $match[1];
				$result["starttime"] = $match[2];
				$result["endhour"] = $match[3];
				$result["endtime"] = $match[4];
			}	
		}
		else
		{
			
				if(is_array($nodesArr["ATTRIBUTES"])){
					if($nodesArr["ATTRIBUTES"]["NAME"]==$name){				
						foreach($nodesArr["ATTRIBUTES"] as $key=>$value){
							$result[$key]=$value;
						}
					}
				}
				$result["Id"]=$nodesArr["ATTRIBUTES"]["NAME"];
				preg_match("/([0-9]{2})([0-9]{2})\-([0-9]{2})([0-9]{2})/si",$result["EFFECTIVETIME"],$match);
				$result["EFFECTIVETIME_Display"]=$match[1].":".$match[2]." - ".$match[3].":".$match[4];
				$result["starthour"] = $match[1];
				$result["starttime"] = $match[2];
				$result["endhour"] = $match[3];
				$result["endtime"] = $match[4];
					
		}	
		$this->recordId = $name;
		return $result;
		 
	}
	
	public function fetchDataSet(){
		$file = OPENBIZ_APP_PATH.DIRECTORY_SEPARATOR.$this->configFile;
		if(!is_file($file)){
			return;
		}
		$configArr=ObjectFactoryHelper::getXmlArray($file);
		$nodesArr = $configArr["APPLICATION"][strtoupper($this->configNode)]["DATABASE"];
		$result = array();				
		
		$name = $configArr["APPLICATION"][strtoupper($this->configNode)]["DATABASE"]["ATTRIBUTES"]["NAME"];
		if(!$name){
			for($i=0;$i<count($nodesArr);$i++){
				if(is_array($nodesArr[$i]["ATTRIBUTES"])){				
					foreach($nodesArr[$i]["ATTRIBUTES"] as $key=>$value){
						$result[$i][$key]=$value;
					}
				}
				$result[$i]["Id"]=$nodesArr[$i]["ATTRIBUTES"]["NAME"];				
			}
			
		}else{
			$this->fixSearchRule = "[Id]='$name'";
			$result[0]=$this->fetchData();
		}
		if(!$this->recordId){
				$this->recordId=$result[0]["Name"];
		}
		return $result;
	}
	
   public function outputAttrs(){
   		$result = parent::outputAttrs();
   		$file = OPENBIZ_APP_PATH.DIRECTORY_SEPARATOR.$this->configFile;
		if(!is_file($file)){
			return;
		}
		$configArr=ObjectFactoryHelper::getXmlArray($file);
   		$this->modeStatus = $configArr["APPLICATION"][strtoupper($this->configNode)]["ATTRIBUTES"]["MODE"];
   		$result['status'] = $this->modeStatus;
   		return $result;   	
   }
      
   protected function getNewRule()
    {
        $recArr = $this->readInputRecord();        
        // load default values if new record value is empty
        $defaultRecArr = array();
        foreach ($this->dataPanel as $element)
        {
            if ($element->fieldName)
            {
                $defaultRecArr[$element->fieldName] = $element->getDefaultValue();
            }
        }

        foreach ($recArr as $field => $val)
        {
            if ( $defaultRecArr[$field] != "" && $val=="")
            {
                $recArr[$field] = $defaultRecArr[$field];
            }
        }
        if(count($recArr)==0){
        	$recArr=$defaultRecArr;
        }
        
        return $recArr;
    }	
    
	public function InsertRecord()
	{
        $recArr = $this->readInputRecord();        
        $this->setActiveRecord($recArr);
        if (count($recArr) == 0)
            return;
                           
            
        try
        {
        	$this->ValidateForm();
	        $name = $recArr['NAME'];
	        $this->validateErrors = array();
	        if($this->checkDupNodeName($name)){	        			       	
	        		$errorMessage = $this->getMessage("FORM_NODE_EXIST",array("fld_name"));
	                $this->validateErrors["fld_name"] = $errorMessage;
	        }
	        if (count($this->validateErrors) > 0)
	        {
	            throw new Openbizx\Validation\Exception($this->validateErrors);
	        }
        }
        catch (Openbizx\Validation\Exception $e)
        {
            $this->processFormObjError($e->errors);
            return;
        }
		$recArr["EFFECTIVETIME"] = $recArr["starthour"].$recArr["starttime"]."-".$recArr["endhour"].$recArr["endtime"];
		$nodeArr = array(
			"ATTRIBUTES" => null,
			"VALUE" => null
		);        
		$recArr["STATUS"]="1";
		foreach($recArr as $key=>$value){
			$nodeArr["ATTRIBUTES"][strtoupper($key)]=$value;
			if(strtoupper($key)=='NAME')
			{
				$newName = $value;
			}
		}   
        $this->addNode($nodeArr);
        $recArr["NAME"]=$newName?$newName: $recArr["NAME"];
        $this->recordId = $recArr["NAME"];
        $this->setActiveRecord($recArr);
        $this->processPostAction();		
	}    

	public function UpdateRecord()
	{
		$recArr = $this->readInputRecord();        
        $this->setActiveRecord($recArr);
        if (count($recArr) == 0)
            return;
        preg_match("/\[(.*?)\]=\'(.*?)\'/si",$this->fixSearchRule,$match);
		$name = $match[2];		
        
		try
        {
        	$this->ValidateForm();
        }
        catch (Openbizx\Validation\Exception $e)
        {
            $this->processFormObjError($e->errors);
            return;
        }
		$nodeArr = array(
			"ATTRIBUTES" => null,
			"VALUE" => null
		);        
		foreach($recArr as $key=>$value){
			$nodeArr["ATTRIBUTES"][strtoupper($key)]=$value;
			if(strtoupper($key)=='NAME')
			{
				$newName = $value;
			}
		}		
		//$nodeArr["ATTRIBUTES"]["NAME"]=$name;
        $this->updateNode($name, $nodeArr);
        
		$this->recordId = $newName?$newName:$name;
        $this->processPostAction();		
	}	
	
	
   
   
   public function deleteRecord($id=null)
    {
        if ($id==null || $id=='')
            $id = Openbizx::$app->getClientProxy()->getFormInputs('_selectedId');

        $selIds = Openbizx::$app->getClientProxy()->getFormInputs('row_selections', false);
        if ($selIds == null)
            $selIds[] = $id;
            
        
        //check prehabit to delete default theme        
        foreach ($selIds as $id)
        {
			if(strtoupper($id)!='DEFAULT'){
        		$this->removeNode($id);    
			}        
        }
        if (strtoupper($this->formType) == "LIST")
            $this->rerender();

        $this->runEventLog();
        $this->processPostAction();
    }	
    
    public function TestConnection($id=null){
        if ($id==null || $id=='')
            $id = Openbizx::$app->getClientProxy()->getFormInputs('_selectedId');

        $selIds = Openbizx::$app->getClientProxy()->getFormInputs('row_selections', false);
        if ($selIds == null)
            $selIds[] = $id;
            
        //check prehabit to delete default theme        
        foreach ($selIds as $id)
        {
            $this->testConnStatus($id);            
        }
        if (strtoupper($this->formType) == "LIST")
            $this->rerender();

        $this->runEventLog();
        $this->processPostAction();    	
    }
	
	private function testConnStatus($name){
		$file = OPENBIZ_APP_PATH.DIRECTORY_SEPARATOR.$this->configFile;
		if(!is_file($file)){
			return;
		}
		$configArr=ObjectFactoryHelper::getXmlArray($file);
		$recordName = $configArr["APPLICATION"][strtoupper($this->configNode)]["DATABASE"]["ATTRIBUTES"]["NAME"];
		if(!$recordName)
		{
			$nodesArr = $configArr["APPLICATION"][strtoupper($this->configNode)]["DATABASE"];
			for($i=0;$i<count($nodesArr);$i++){
				if(is_array($nodesArr[$i]["ATTRIBUTES"])){					
					if($nodesArr[$i]["ATTRIBUTES"]["NAME"]==$name){	
						
						$rec = $nodesArr[$i]["ATTRIBUTES"];
						
						$server = $rec['SERVER'];	
			    		$port 	= $rec['PORT'];
			    		$driver	= $rec['DRIVER'];
			    		$username= $rec['USER'];
			    		$password 	= $rec['PASSWORD'];
			    		$charset 	= $rec['CHARSET'];
			    		$dbname 	= $rec['DBNAME'];
						
						$dbconn = @mysql_connect($server.":".$port,$username,$password);
        				$dblist = @mysql_list_dbs($dbconn); 
						
						$connStatus = 0;
						while ($row = @mysql_fetch_array($dblist)){
		        			if($row['Database']==$dbname){
		        				$connStatus = 1; 
		        				break;
		        			}
		        		}
						$configArr["APPLICATION"][strtoupper($this->configNode)]["DATABASE"][$i]["ATTRIBUTES"]['STATUS']=$connStatus;
					}
				}
			}
		}
		else
		{			
			$rec = $configArr["APPLICATION"][strtoupper($this->configNode)]["DATABASE"]["ATTRIBUTES"];
						
						$server = $rec['SERVER'];	
			    		$port 	= $rec['PORT'];
			    		$driver	= $rec['DRIVER'];
			    		$username= $rec['USER'];
			    		$password 	= $rec['PASSWORD'];
			    		$charset 	= $rec['CHARSET'];
			    		$dbname 	= $rec['DBNAME'];
						
						$dbconn = @mysql_connect($server.":".$port,$username,$password);
        				$dblist = @mysql_list_dbs($dbconn); 
						
						$connStatus = 0;
						while ($row = @mysql_fetch_array($dblist)){
		        			if($row['Database']==$dbname){
		        				$connStatus = 1; 
		        				break;
		        			}
		        		}
		        		$configArr["APPLICATION"][strtoupper($this->configNode)]["DATABASE"]["ATTRIBUTES"]['STATUS']=$connStatus;
			
		}
		$this->saveToXML($configArr);	
	}
    
	private function addNode($nodeArr){
		$file = OPENBIZ_APP_PATH.DIRECTORY_SEPARATOR.$this->configFile;
		if(!is_file($file)){
			return;
		}
		$configArr=ObjectFactoryHelper::getXmlArray($file);		
		$recordName = $configArr["APPLICATION"][strtoupper($this->configNode)]["DATABASE"]["ATTRIBUTES"]["NAME"];
		$recordCount = count($configArr["APPLICATION"][strtoupper($this->configNode)]["DATABASE"]);
		if(!$recordName && $recordCount){
			array_push($configArr["APPLICATION"][strtoupper($this->configNode)]["DATABASE"] , $nodeArr);			
		}
		elseif($recordCount)
		{
			$oldNodeArr = $configArr["APPLICATION"][strtoupper($this->configNode)]["DATABASE"];
			$configArr["APPLICATION"][strtoupper($this->configNode)]["DATABASE"]=array();
			array_push($configArr["APPLICATION"][strtoupper($this->configNode)]["DATABASE"] , $nodeArr);
			array_push($configArr["APPLICATION"][strtoupper($this->configNode)]["DATABASE"] , $oldNodeArr);
		}else{
			$configArr["APPLICATION"][strtoupper($this->configNode)]["DATABASE"] = $nodeArr;
		}
		$this->saveToXML($configArr);	
		$this->TestConnection($nodeArr["ATTRIBUTES"]["NAME"]);	
	}
	
	private function updateNode($name, $nodeArr){
		$file = OPENBIZ_APP_PATH.DIRECTORY_SEPARATOR.$this->configFile;
		if(!is_file($file)){
			return;
		}
		$configArr=ObjectFactoryHelper::getXmlArray($file);
		$recordName = $configArr["APPLICATION"][strtoupper($this->configNode)]["DATABASE"]["ATTRIBUTES"]["NAME"];
		if(!$recordName){
			$nodesArr = $configArr["APPLICATION"][strtoupper($this->configNode)]["DATABASE"];
			for($i=0;$i<count($nodesArr);$i++){
				if(is_array($nodesArr[$i]["ATTRIBUTES"])){
					if($nodesArr[$i]["ATTRIBUTES"]["NAME"]==$name){	
						$configArr["APPLICATION"][strtoupper($this->configNode)]["DATABASE"][$i]=$nodeArr;
						break;
					}
				}
			}
		}
		else
		{
			$configArr["APPLICATION"][strtoupper($this->configNode)]["DATABASE"]=$nodeArr;
		}
		$this->saveToXML($configArr);		
		$this->TestConnection($name);
	}
	
	private function removeNode($name){
		$file = OPENBIZ_APP_PATH.DIRECTORY_SEPARATOR.$this->configFile;
		if(!is_file($file)){
			return;
		}
		$configArr=ObjectFactoryHelper::getXmlArray($file);
		$recordName = $configArr["APPLICATION"][strtoupper($this->configNode)]["DATABASE"]["ATTRIBUTES"]["NAME"];
		if(!$recordName)
		{
			$nodesArr = $configArr["APPLICATION"][strtoupper($this->configNode)]["DATABASE"];
			for($i=0;$i<count($nodesArr);$i++){
				if(is_array($nodesArr[$i]["ATTRIBUTES"])){					
					if($nodesArr[$i]["ATTRIBUTES"]["NAME"]==$name){	
						unset($configArr["APPLICATION"][strtoupper($this->configNode)]["DATABASE"][$i]);
					}
				}
			}
		}
		else
		{
			unset($configArr["APPLICATION"][strtoupper($this->configNode)]["DATABASE"]);
		}
		$this->saveToXML($configArr);
	}
	
	private function checkDupNodeName($nodeName){
		$file = OPENBIZ_APP_PATH.DIRECTORY_SEPARATOR.$this->configFile;
		if(!is_file($file)){
			return;
		}
		$configArr=ObjectFactoryHelper::getXmlArray($file);
		$recordName = $configArr["APPLICATION"][strtoupper($this->configNode)]["DATABASE"]["ATTRIBUTES"]["NAME"];
		if(!$recordName)
		{
			$nodesArr = $configArr["APPLICATION"][strtoupper($this->configNode)]["DATABASE"];
			$result = array();
			
			for($i=0;$i<count($nodesArr);$i++){
				if(is_array($nodesArr[$i]["ATTRIBUTES"])){
					if($nodesArr[$i]["ATTRIBUTES"]["NAME"]==$nodeName){				
						return true;
					}
				}
			}
		}
		else
		{
			if($recordName==$nodeName){
				return true;
			}
		}	
		return false;	
	}
	
	private function saveToXML($data){
		$smarty = TemplateHelper::getSmartyTemplate();
		$smarty->assign("data", $data);
		$xmldata = $smarty->fetch(TemplateHelper::getTplFileWithPath("applicationTemplate.xml.tpl", $this->package));
		$service_dir = OPENBIZ_APP_PATH;
		$service_file = $service_dir.DIRECTORY_SEPARATOR.$this->configFile;
		file_put_contents($service_file ,$xmldata);		
		return true;
	}	
}
