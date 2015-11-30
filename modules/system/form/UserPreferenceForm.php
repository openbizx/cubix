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
 * @version   $Id: UserPreferenceForm.php 4746 2012-11-15 08:52:16Z hellojixian@gmail.com $
 */

use Openbizx\Openbizx;
use Openbizx\Data\Helpers\QueryStringParam;
use Openbizx\Easy\EasyForm;

/**
 * UserPreferenceForm class - implement the logic of setting user preferences
 *
 * @access public
 */
class UserPreferenceForm extends EasyForm
{
    protected $_userId = null;
    
    function __construct(&$xmlArr)
    {
        parent::__construct($xmlArr);        
        $this->_userId = 0;
    }
    
    public function allowAccess(){
    	return parent::allowAccess();
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
        if($prefRecord["_siteurl"]=="")
        {
        	if($_SERVER["HTTPS"])
        	{
        		$prefRecord["_siteurl"]="https://".$_SERVER["SERVER_NAME"].OPENBIZ_APP_URL;
        	}
        	else
        	{
        		$prefRecord["_siteurl"]="http://".$_SERVER["SERVER_NAME"].OPENBIZ_APP_URL;	
        	}        	
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
	            
	            //update default app_init setting
	            $config_file = OPENBIZ_APP_PATH.'/bin/app_init.php';
	            switch($name){
	            	case "theme":
	            		if($value!=CUBI_DEFAULT_THEME_NAME){
	            			//update default theme CUBI_DEFAULT_THEME_NAME
	            			$data = file_get_contents($config_file);	            			
	            			$data = preg_replace("/define\([\'\\\"]{1}CUBI_DEFAULT_THEME_NAME[\'\\\"]{1}.*?\)\;/i","define('CUBI_DEFAULT_THEME_NAME','$value');",$data);	            			
	            			@file_put_contents($config_file,$data);
	            		}
	            		break;
	            	case "system_name":
	            		if($value!=OPENBIZ_DEFAULT_SYSTEM_NAME){
	            			//update default theme CUBI_DEFAULT_THEME_NAME
	            			$data = file_get_contents($config_file);	            			
	            			$data = preg_replace("/define\([\'\\\"]{1}OPENBIZ_DEFAULT_SYSTEM_NAME[\'\\\"]{1}.*?\)\;/i","define('OPENBIZ_DEFAULT_SYSTEM_NAME','$value');",$data);	            			
	            			@file_put_contents($config_file,$data);
	            		}
	            		break;
					case "siteurl":
						//update default theme SITE_URL
            			$data = file_get_contents($config_file);	            			
            			$data = preg_replace("/define\([\'\\\"]{1}SITE_URL[\'\\\"]{1}.*?\)\;/i","define('SITE_URL','$value');",$data);	            			
            			@file_put_contents($config_file,$data);
	            		break;	   	            		    
	            	case "sessionstrict":
						//update default theme CUBI_SESSION_STRICT
	            		if($value!=CUBI_SESSION_STRICT){
	            			$data = file_get_contents($config_file);	            			
	            			$data = preg_replace("/define\([\'\\\"]{1}CUBI_SESSION_STRICT[\'\\\"]{1}.*?\)\;/i","define('CUBI_SESSION_STRICT','$value');",$data);	            			
	            			@file_put_contents($config_file,$data);
	            		}
	            		break;	
	            	case "sessiontimeout":
						//update default theme TIMEOUT
	            		if($value!=OPENBIZ_TIMEOUT){
	            			$data = file_get_contents($config_file);	            			
	            			$data = preg_replace("/define\([\'\\\"]{1}OPENBIZ_TIMEOUT[\'\\\"]{1}.*?\)\;/i","define('OPENBIZ_TIMEOUT','$value');",$data);	            			
	            			@file_put_contents($config_file,$data);
	            		}
	            		break;	
	            	case "data_acl":
						//update default theme CUBI_DATA_ACL
	            		if($value!=CUBI_DATA_ACL){
	            			$data = file_get_contents($config_file);	            			
	            			$data = preg_replace("/define\([\'\\\"]{1}CUBI_DATA_ACL[\'\\\"]{1}.*?\)\;/i","define('CUBI_DATA_ACL','$value');",$data);	            			
	            			@file_put_contents($config_file,$data);
	            		}
	            		break;	     		
	            	case "language":
	            	    if($value!=OPENBIZ_DEFAULT_LANGUAGE){
	            			//update default theme OPENBIZ_DEFAULT_LANGUAGE
	            			$data = file_get_contents($config_file);	            			
	            			$data = preg_replace("/define\([\'\\\"]{1}OPENBIZ_DEFAULT_LANGUAGE[\'\\\"]{1}.*?\)\;/i","define('OPENBIZ_DEFAULT_LANGUAGE','$value');",$data);	            			
	            			@file_put_contents($config_file,$data);	   

	            			//make changes now
	            			Openbizx::$app->getSessionContext()->setVar("LANG",$value );
	            		}
	            		break;
	            	case "currency":
	            	    if($value!=CUBI_DEFAULT_CURRENCY){
	            			//update default theme CUBI_DEFAULT_CURRENCY
	            			$data = file_get_contents($config_file);	            			
	            			$data = preg_replace("/define\([\'\\\"]{1}CUBI_DEFAULT_CURRENCY[\'\\\"]{1}.*?\)\;/i","define('CUBI_DEFAULT_CURRENCY','$value');",$data);	            			
	            			@file_put_contents($config_file,$data);	            			
	            		}
	            		break;	
	            	case "appbuilder":
	            	    if($value!=CUBI_APPBUILDER){	            			
	            			$data = file_get_contents($config_file);	            			
	            			$data = preg_replace("/define\([\'\\\"]{1}CUBI_APPBUILDER[\'\\\"]{1}.*?\)\;/i","define('CUBI_APPBUILDER','$value');",$data);	            			
	            			@file_put_contents($config_file,$data);	            			
	            		}
	            		break;
	            	case "debug":
	            	    if($value!=OPENBIZ_DEBUG){	            			
	            			$data = file_get_contents($config_file);	            			
	            			$data = preg_replace("/define\([\'\\\"]{1}OPENBIZ_DEBUG[\'\\\"]{1}.*?\)\;/i","define('OPENBIZ_DEBUG','$value');",$data);	            			
	            			@file_put_contents($config_file,$data);	            			
	            		}
	            		break;
	            	case "timezone":
	            	    if($value!=CUBI_DEFAULT_TIMEZONE){
	            			//update default theme CUBI_DEFAULT_THEME_NAME
	            			$data = file_get_contents($config_file);	            			
	            			$data = preg_replace("/define\([\'\\\"]{1}CUBI_DEFAULT_TIMEZONE[\'\\\"]{1}.*?\)\;/i","define('CUBI_DEFAULT_TIMEZONE','$value');",$data);	            			
	            			@file_put_contents($config_file,$data);	            			
	            		}
	            		break;
	            	case "group_data_share":
	            	    if($value!=CUBI_GROUP_DATA_SHARE){
	            			$data = file_get_contents($config_file);	            			
	            			$data = preg_replace("/define\([\'\\\"]{1}CUBI_GROUP_DATA_SHARE[\'\\\"]{1}.*?\)\;/i","define('CUBI_GROUP_DATA_SHARE','$value');",$data);	            			
	            			@file_put_contents($config_file,$data);	            			
	            		}
	            		break;	    
	            	case "owner_perm":
	            	    if($value!=OPENBIZ_DEFAULT_OWNER_PERM){
	            			$data = file_get_contents($config_file);	            			
	            			$data = preg_replace("/define\([\'\\\"]{1}OPENBIZ_DEFAULT_OWNER_PERM[\'\\\"]{1}.*?\)\;/i","define('OPENBIZ_DEFAULT_OWNER_PERM','$value');",$data);	            			
	            			@file_put_contents($config_file,$data);	            			
	            		}
	            		break;	
	            	case "group_perm":
	            	    if($value!=OPENBIZ_DEFAULT_GROUP_PERM){
	            			$data = file_get_contents($config_file);	            			
	            			$data = preg_replace("/define\([\'\\\"]{1}OPENBIZ_DEFAULT_GROUP_PERM[\'\\\"]{1}.*?\)\;/i","define('OPENBIZ_DEFAULT_GROUP_PERM','$value');",$data);	            			
	            			@file_put_contents($config_file,$data);	            			
	            		}
	            		break;	
	            	case "other_perm":
	            	    if($value!=OPENBIZ_DEFAULT_OTHER_PERM){
	            			$data = file_get_contents($config_file);	            			
	            			$data = preg_replace("/define\([\'\\\"]{1}OPENBIZ_DEFAULT_OTHER_PERM[\'\\\"]{1}.*?\)\;/i","define('OPENBIZ_DEFAULT_OTHER_PERM','$value');",$data);	            			
	            			@file_put_contents($config_file,$data);	            			
	            		}
	            		break;		            			            			            		        		
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
