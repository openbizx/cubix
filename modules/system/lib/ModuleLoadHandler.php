<?php

/**
 * Openbizx Cubi Application Platform
 *
 * LICENSE http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 *
 * @package   cubi.system.lib
 * @copyright Copyright (c) 2005-2011, Openbiz Technology LLC
 * @license   http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 * @link      http://code.google.com/p/openbiz-cubi/
 * @version   $Id: ModuleLoadHandler.php 4963 2012-12-28 08:35:35Z hellojixian@gmail.com $
 */
use Openbizx\Openbizx;

include_once (Openbizx::$app->getModulePath() . "/system/lib/ModuleLoader.php");

interface ModuleLoadHandler
{

    public function beforeLoadingModule($moduelLoader);

    public function postLoadingModule($moduelLoader);
}

class DefaultModuleLoadHandler implements ModuleLoadHandler
{

    protected $roleName;
    protected $moduleName;

    public function beforeLoadingModule($moduelLoader)
    {
        
    }

    public function postLoadingModule($moduelLoader)
    {

        $roleRec = Openbizx::getObject("system.do.RoleDO")->fetchOne("[name]='{$this->roleName}'");
        $memberRoleId = $roleRec['Id'];

        $actionList = Openbizx::getObject("system.do.AclActionDO")->directfetch("[module]='{$this->moduleName}'");
        foreach ($actionList as $actionRec) {
            $actionId = $actionRec["Id"];

            $aclRecord = array(
                "role_id" => $memberRoleId,
                "action_id" => $actionId,
                "access_level" => 1
            );
            Openbizx::getObject("system.do.AclRoleActionDO")->insertRecord($aclRecord);
        }
    }

    public function beforeUnloadModule($moduelLoader)
    {
        
    }

    public function postUnloadModule($moduleLoader)
    {
        $roleRec = Openbizx::getObject("system.do.RoleDO")->fetchOne("[name]='{$this->roleName}'");
        $memberRoleId = $roleRec['Id'];
        $roleRec->delete();

        $actionList = Openbizx::getObject("system.do.AclActionDO")->directfetch("[module]='{$this->moduleName}'");
        foreach ($actionList as $actionRec) {
            $actionId = $actionRec["Id"];
            Openbizx::getObject("system.do.AclRoleActionDO")->deleteRecords("[action_id]='$actionId' AND [role_id]='$memberRoleId'");
        }

        Openbizx::getObject("system.do.AclActionDO")->deleteRecords("[module]='{$this->moduleName}'");
    }

}