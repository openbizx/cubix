<?php

use Openbizx\Openbizx;
include_once (Openbizx::$app->getModulePath()."/system/lib/ModuleLoadHandler.php");

class OAuthLoadHandler implements ModuleLoadHandler
{
    public function beforeLoadingModule($moduelLoader)
    {
    }
    
    public function postLoadingModule($moduelLoader)
    {
    	
    	$roleRec = Openbizx::getObject("system.do.RoleDO")->fetchOne("[name]='Cubi Member'");
    	$roleId = $roleRec['Id'];
    	
    	$actionRec = Openbizx::getObject("system.do.AclActionDO")->fetchOne("[module]='oauth' AND [resource]='oauth' AND [action]='MyAccount'");
    	$actionId = $actionRec["Id"];
    	
    	$aclRecord = array(
    		"role_id" =>  $roleId,
    		"action_id" => $actionId,
    		"access_level" => 1
    	);
    	Openbizx::getObject("system.do.AclRoleActionDO")->insertRecord($aclRecord);
    }

}

