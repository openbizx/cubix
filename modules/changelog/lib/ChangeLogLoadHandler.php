<?php
/**
 * Openbizx Cubi Application Platform
 *
 * LICENSE http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 *
 * @package   cubi.changelog.lib
 * @copyright Copyright (c) 2005-2011, Openbiz Technology LLC
 * @license   http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 * @link      http://code.google.com/p/openbiz-cubi/
 * @version   $Id: ChangeLogLoadHandler.php 3354 2012-05-31 05:39:37Z rockyswen@gmail.com $
 */

use Openbizx\Openbizx;

include_once (Openbizx::$app->getModulePath()."/system/lib/ModuleLoadHandler.php");

class ChangelogLoadHandler implements ModuleLoadHandler
{
    public function beforeLoadingModule($moduelLoader)
    {
    }
    
    public function postLoadingModule($moduelLoader)
    {
    	
    	$roleRec = Openbizx::getObject("system.do.RoleDO")->fetchOne("[name]='Cubi Member'");
    	$roleId = $roleRec['Id'];
    	
    	$actionRec = Openbizx::getObject("system.do.AclActionDO")->fetchOne("[module]='changelog' AND [resource]='changelog' AND [action]='access'");
    	$actionId = $actionRec["Id"];
    	
    	$aclRecord = array(
    		"role_id" =>  $roleId,
    		"action_id" => $actionId,
    		"access_level" => 1
    	);
    	Openbizx::getObject("system.do.AclRoleActionDO")->insertRecord($aclRecord);
    }
}

