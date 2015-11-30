<?php
/**
 * Openbizx Cubi Application Platform
 *
 * LICENSE http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 *
 * @package   cubi.help.lib
 * @copyright Copyright (c) 2005-2011, Openbiz Technology LLC
 * @license   http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 * @link      http://code.google.com/p/openbiz-cubi/
 * @version   $Id: HelpLoadHandler.php 3783 2012-08-01 12:16:45Z hellojixian@gmail.com $
 */

use Openbizx\Openbizx;

include_once (Openbizx::$app->getModulePath()."/system/lib/ModuleLoadHandler.php");

class HelpLoadHandler implements ModuleLoadHandler
{
    public function beforeLoadingModule($moduelLoader)
    {
    }
    
    public function postLoadingModule($moduelLoader)
    {
    	
    	$roleRec = Openbizx::getObject("system.do.RoleDO")->fetchOne("[name]='Cubi Member'");
    	$roleId = $roleRec['Id'];
    	
    	$actionRec = Openbizx::getObject("system.do.AclActionDO")->fetchOne("[module]='help' AND [resource]='Help' AND [action]='Access_Widget'");
    	$actionId = $actionRec["Id"];
    	
    	$aclRecord = array(
    		"role_id" =>  $roleId,
    		"action_id" => $actionId,
    		"access_level" => 1
    	);
    	Openbizx::getObject("system.do.AclRoleActionDO")->insertRecord($aclRecord);
    }
}

