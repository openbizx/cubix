<?php
/**
 * Openbizx Cubi Application Platform
 *
 * LICENSE http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 *
 * @package   cubi.user.form
 * @copyright Copyright (c) 2005-2011, Openbiz Technology LLC
 * @license   http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 * @link      http://code.google.com/p/openbiz-cubi/
 * @version   $Id: RegisterForm.php 4303 2012-09-26 05:48:29Z hellojixian@gmail.com $
 */

use Openbizx\Openbizx;

include_once(Openbizx::$app->getModulePath()."/system/form/UserForm.php");

class RegisterForm extends UserForm
{
/**
     * Create a user record
     *
     * @return void
     */
    public function CreateUser()
    {
       	$userinfo = $this->_doCreateUser();		
        $this->processPostAction();
    }
    
    protected function _doCreateUser()
    {
 		$recArr = $this->readInputRecord();
        $this->setActiveRecord($recArr);
        if (count($recArr) == 0)
            return;

        if ($this->_checkDupUsername())
        {
            $errorMessage = $this->GetMessage("USERNAME_USED");
			$errors['fld_username'] = $errorMessage;
			$this->processFormObjError($errors);
			return;
        }

        if ($this->_checkDupEmail())
        {
            $errorMessage = $this->GetMessage("EMAIL_USED");
			$errors['fld_email'] = $errorMessage;
			$this->processFormObjError($errors);
			return;
        }
                
        try
        {
            $this->ValidateForm();
        }
        catch (Openbizx\Validation\Exception $e)
        {
            $this->processFormObjError($e->errors);
            return;
        }
        
        $recArr['create_by']="0";
        $recArr['update_by']="0";

        $password = Openbizx::$app->getClientProxy()->GetFormInputs("fld_password");            
		$recArr['password'] = hash(HASH_ALG, $password);
        
        $this->_doInsert($recArr);
                
        //set default user role to member
		$userinfo = $this->getActiveRecord();
        $userRoleObj = Openbizx::getObject('system.do.UserRoleDO');
        foreach( Openbizx::getObject('system.do.RoleDO')->directfetch("[default]='1'") as $roleRec)
        {
        	$roleId = $roleRec['Id'];
        	$uesrRoleArr =array(
        				"user_id"=>$userinfo['Id'],
        				"role_id"=>$roleId, 
        				);          
        	$userRoleObj->insertRecord($uesrRoleArr);
        }
		
        //set default group to member
        $userGroupObj = Openbizx::getObject('system.do.UserGroupDO');
        foreach( Openbizx::getObject('system.do.GroupDO')->directfetch("[default]='1'") as $groupRec)
        {
			$groupId = $groupRec['Id'];
			$uesrGroupArr =array(
        				"user_id"=>$userinfo['Id'],
        				"group_id"=>$groupId,  
        				);
        	$userGroupObj->insertRecord($uesrGroupArr); 			
        }
        
        
        //record event log   
        $eventlog 	= Openbizx::getService(OPENBIZ_EVENTLOG_SERVICE);
        $logComment=array($userinfo['username'],$_SERVER['REMOTE_ADDR']);
    	$eventlog->log("USER_MANAGEMENT", "MSG_USER_REGISTERED", $logComment);   
    	     
        //send user email
        $emailObj 	= Openbizx::getService(CUBI_USER_EMAIL_SERVICE);
        $emailObj->UserWelcomeEmail($userinfo['Id']);
        
        //init profile for future use like redirect to my account view
        $profile = Openbizx::$app->InituserProfile($userinfo['username']);    	
        
        return $userinfo;
    }
}

