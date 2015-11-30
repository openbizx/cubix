<?php
/**
 * Openbizx Cubi Application Platform
 *
 * LICENSE http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 *
 * @package   cubi.myaccount.form
 * @copyright Copyright (c) 2005-2011, Openbiz Technology LLC
 * @license   http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 * @link      http://code.google.com/p/openbiz-cubi/
 * @version   $Id: ResetPasswordForm.php 5283 2013-01-28 06:05:39Z fsliit@gmail.com $
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
 * @version   $Id: ResetPasswordForm.php 5283 2013-01-28 06:05:39Z fsliit@gmail.com $
 */

use Openbizx\Openbizx;

/**
 * ForgetPassForm class - implement the logic of forget password form
 *
 * @package user.form
 * @author Jixian Wang
 * @copyright Copyright (c) 2005-2009
 * @access public
 */
include_once(Openbizx::$app->getModulePath()."/system/form/UserForm.php");

class ResetPasswordForm extends UserForm
{
	public $defaultLogoff = 'Y';
	
	public function fetchData()
	{
		if($this->getWebpageObject()->isForceResetPassword())
		{
			$this->defaultLogoff = 'N';
		}else{
			$this->defaultLogoff = 'Y';
		}
		return parent::fetchData();
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
    
    public function resetPassword()
    {
        $currentRec = $this->fetchData();        
        $recArr = $this->readInputRecord();
        $this->setActiveRecord($recArr);
        try
        {
            $this->ValidateForm();
        }
        catch (Openbizx\Validation\Exception $e)
        {
        	$this->processFormObjError($e->errors);
            return;
        }
        //check old password
        $old_password = Openbizx::$app->getClientProxy()->GetFormInputs("fld_password_old");
        if(!$this->checkPassword($old_password)){
        	$error = array("fld_password_old"=>$this->GetMessage("OLD_PASSOWRD_INVAILD"));
        	$this->processFormObjError($error);
        	return;
        }

        $password = Openbizx::$app->getClientProxy()->GetFormInputs("fld_password");            
        if($password){
        	$recArr['password'] = hash(HASH_ALG, $password);
		}        
        
        if (count($recArr) == 0)
            return;

        $this->_doUpdate($recArr, $currentRec);
        
        // if 'notify email' option is checked, send confirmation email to user email address
        // ...

		// init profile
	    $profile = Openbizx::$app->InitUserProfile($currentRec['username']);
    				       	
       	//run eventlog
        $eventlog 	= Openbizx::getService(OPENBIZ_EVENTLOG_SERVICE);
        $logComment=array($currentRec['username']);
    	$eventlog->log("USER_MANAGEMENT", "MSG_RESET_PASSWORD_BY_TOKEN", $logComment);       	
	    
        $this->notices[] = $this->GetMessage("USER_DATA_UPDATED");                
        $this->updateForm();
 
        if( $this->getWebpageObject()->isForceResetPassword() )
        {
        	Openbizx::getService(OPENBIZ_PREFERENCE_SERVICE)->setPreference('force_change_passwd',0);
        	$profileDefaultPageArr = Openbizx::$app->getUserProfile('roleStartpage');
        	$pageURL = OPENBIZ_APP_INDEX_URL.$profileDefaultPageArr[0];
        	Openbizx::$app->getClientProxy()->redirectPage($pageURL);
        }        
        
        if($recArr['_logoff']==1)
        {        	
			$url = OPENBIZ_APP_INDEX_URL."/user/logout";
			Openbizx::$app->getClientProxy()->redirectPage($url);	
        }
    }
    
    public function checkPassword($password){
    	if(!$password){
    		return false;
    	}
    	$currentRec = $this->fetchData();
    	$username= $currentRec['username'];
    	$svcobj 	= Openbizx::getService(AUTH_SERVICE);
    	$result = $svcobj->authenticateUser($username,$password);
    	return $result;
    	
    }
        
    private function validateToken($token){
    	if(empty($token))
    	{
    		return false;
    	}
    	
    	$tokenObj = Openbizx::getObject('system.do.UserPassTokenDO');
        $tokenArr = $tokenObj->directFetch("[token]='$token'", 1);
        if(count($tokenArr)==1)
        {
        	$tokenArr = $tokenArr[0];
        	$expiration = $tokenArr['expiration'];
        	if(strtotime($expiration) > time() )
        	{
        		return $tokenArr['user_id'];
        	}else{
        		return false;
        	}
        }
        else
        {
        	return false;
        }
        return true;
    }
    
	public function validateForm()
    {	
    
    	//validate password
    	$password = Openbizx::$app->getClientProxy()->GetFormInputs("fld_password");
		$validateSvc = Openbizx::getService(VALIDATE_SERVICE);
		if(!$validateSvc->betweenLength($password,6,50))
		{
			$errorMessage = $this->GetMessage("PASSWORD_LENGTH");
			$this->validateErrors['fld_password'] = $errorMessage;
			throw new Openbizx\Validation\Exception($this->validateErrors);
			return false;
		}
		
    	// disable password validation if they are empty
    	$password = Openbizx::$app->getClientProxy()->GetFormInputs("fld_password");
		$password_repeat = Openbizx::$app->getClientProxy()->GetFormInputs("fld_password_repeat");
    	if (!$password_repeat)
    	    $this->getElement("fld_password")->validator = null;
    	if (!$password)
    	    $this->getElement("fld_password_repeat")->validator = null;

    	
        
		if($password != "" && ($password != $password_repeat))
		{
			$passRepeatElem = $this->getElement("fld_password_repeat");
			$errorMessage = $this->GetMessage("PASSOWRD_REPEAT_NOTSAME",array($passRepeatElem->label));
			$this->validateErrors['fld_password_repeat'] = $errorMessage;
			throw new Openbizx\Validation\Exception($this->validateErrors);
			return false;
		}
	
        return true;
    }    
    
    protected function _checkDupUsername(){
    	//its not gonna change username so doesnt matter
    	return false;
    }
    protected function _checkDupEmail(){
    	//its not gonna change email address so doesnt matter
    	return false;
    }    
}  
