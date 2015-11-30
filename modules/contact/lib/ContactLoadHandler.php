<?php
include_once (Openbizx::$app->getModulePath()."/system/lib/ModuleLoadHandler.php");

class ContactLoadHandler extends DefaultModuleLoadHandler 
{
	protected $roleName = 'Contact Member';
	protected $moduleName = 'contact';   
}
