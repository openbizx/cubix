<?php
/**
 * Openbizx Cubi Application Platform
 *
 * LICENSE http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 *
 * @package   cubi.common.lib
 * @copyright Copyright (c) 2005-2011, Openbiz Technology LLC
 * @license   http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 * @link      http://code.google.com/p/openbiz-cubi/
 * @version   $Id$
 */

use Openbizx\Openbizx;

include_once (Openbizx::$app->getModulePath()."/system/lib/ModuleLoadHandler.php");

class CommonLoadHandler implements ModuleLoadHandler
{
    public function beforeLoadingModule($moduelLoader)
    {
    }
    
    public function postLoadingModule($moduelLoader)
    {
    	
    	$roleRec = Openbizx::getObject("system.do.RoleDO")->fetchOne("[name]='Collaboration Admin'");
    	$adminRoleId = $roleRec['Id'];
    	
    	$roleRec = Openbizx::getObject("system.do.RoleDO")->fetchOne("[name]='Data Manager'");
    	$managerRoleId = $roleRec['Id'];
    	
    	$roleRec = Openbizx::getObject("system.do.RoleDO")->fetchOne("[name]='Data Assigner'");
    	$assignerRoleId = $roleRec['Id'];
    	
    	//set up acls for Data assigner
    	$actionList = Openbizx::getObject("system.do.AclActionDO")->directfetch("[module]='common' AND [resource]='data_assign'");
    	foreach ($actionList as $actionRec){
	    	$actionId = $actionRec["Id"];
	    	
	    	$aclRecord = array(
	    		"role_id" =>  $assignerRoleId,
	    		"action_id" => $actionId,
	    		"access_level" => 1
	    	);
	    	Openbizx::getObject("system.do.AclRoleActionDO")->insertRecord($aclRecord);
		     	
	    	$aclRecord = array(
	    		"role_id" =>  $managerRoleId,
	    		"action_id" => $actionId,
	    		"access_level" => 1
	    	);
	    	Openbizx::getObject("system.do.AclRoleActionDO")->insertRecord($aclRecord);	    	
    	}
    	
        //set up acls for Data manager
    	$actionList = Openbizx::getObject("system.do.AclActionDO")->directfetch("[module]='common' AND [resource]='data_manage'");
    	foreach ($actionList as $actionRec){
	    	$actionId = $actionRec["Id"];	    	
	    	$aclRecord = array(
	    		"role_id" =>  $managerRoleId,
	    		"action_id" => $actionId,
	    		"access_level" => 1
	    	);
	    	Openbizx::getObject("system.do.AclRoleActionDO")->insertRecord($aclRecord);	    	
    	}    

    	//delete data manage permission from admin
    	$actionRec = Openbizx::getObject("system.do.AclActionDO")->fetchOne("[module]='common' AND [resource]='data_manage' AND [action]='manage'");
    	$actionId = $actionRec['Id'];
    	Openbizx::getObject("system.do.AclRoleActionDO",1)->deleteRecords("[role_id]='$adminRoleId' AND [action_id]='$actionId'");
    }
}

