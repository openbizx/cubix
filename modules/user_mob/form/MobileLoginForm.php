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
 * @version   $Id: LoginForm.php 3569 2012-07-10 03:06:09Z hellojixian@gmail.com $
 */

use Openbizx\Openbizx;

/**
 * LoginForm class - implement the logic of login form
 *
 * @package user.form
 * @author Rocky Swen
 * @copyright Copyright (c) 2005-2009
 * @access public
 */
include_once (Openbizx::$app->getModulePath()."/user/form/LoginForm.php");
 
class MobileLoginForm extends LoginForm
{
	protected $MOBILE_STARTPAGE = "/user_mob/user_home";
    /**
     * login action
     *
     * @return void
     */
    public function Login()
    {
	  	$this->MOBILE_STARTPAGE = OPENBIZ_APP_INDEX_URL.$this->MOBILE_STARTPAGE;
		$recArr = $this->readInputRecord();	  	
	  	try
        {
            $this->ValidateForm();
        }
        catch (Openbizx\Validation\Exception $e)
        {        	
            $this->processFormObjError($e->errors);
            return;
        }
	  	        
	  	// get the username and password	
		$this->username = Openbizx::$app->getClientProxy()->getFormInputs("username");
		$this->password = Openbizx::$app->getClientProxy()->getFormInputs("password");		
		$this->smartcard = Openbizx::$app->getClientProxy()->getFormInputs("smartcard");
		
		if($this->username == $this->getElement("username")->hint){
			$this->username = null;
		}
    	if($this->password == $this->getElement("password")->hint){
			$this->password = null;
		}
		
		$eventlog 	= Openbizx::getService(OPENBIZ_EVENTLOG_SERVICE);
		try {
    		if ($this->authUser()) 
    		{
                // after authenticate user: 1. init profile
    			$profile = Openbizx::$app->InitUserProfile($this->username);
    	   	   
    			// after authenticate user: 2. insert login event
    			$logComment=array(	$this->username, $_SERVER['REMOTE_ADDR']);
    			$eventlog->log("LOGIN", "MSG_LOGIN_SUCCESSFUL", $logComment);
    			
    			// after authenticate user: 3. update login time in user record
    	   	    if (!$this->UpdateLoginTime())
    	   	        return false;
    	   	            	   	        
    	   	    // after authenticate user: 3. update current theme and language
       			$currentLanguage = Openbizx::$app->getClientProxy()->getFormInputs("current_language");
   				if($currentLanguage!=''){
   				   	if($currentLanguage=='user_default'){
		   				$currentTheme = OPENBIZ_DEFAULT_LANGUAGE;
		   			}else{
       					Openbizx::$app->getSessionContext()->setVar("LANG",$currentLanguage );
		   			}
   				}

				$currentTheme = Openbizx::$app->getClientProxy()->getFormInputs("current_theme");
				if($currentTheme!=''){
					if($currentTheme=='user_default'){
		   				$currentTheme = CUBI_DEFAULT_THEME_NAME;
		   			}else{
   						Openbizx::$app->getSessionContext()->setVar("THEME",$currentTheme );
		   			}
				}
    	   	   		
    	   	    $redirectPage = OPENBIZ_APP_INDEX_URL.$profile['roleStartpage'][0];
    	   	   	if(!$profile['roleStartpage'][0])
    	   	   	{
    	   	   		$errorMessage['password'] = $this->getMessage("PERM_INCORRECT");
    	   	   		$errorMessage['login_status'] = $this->getMessage("LOGIN_FAILED");    			    			
    				$this->processFormObjError($errorMessage);
    				return;
    	   	   	}
    	   	    $cookies = Openbizx::$app->getClientProxy()->getFormInputs("session_timeout");
    	   	    if($cookies)
    	   	    {
    	   	    	$password = $this->password;    	   	    	
    	   	    	$password = md5(md5($password.$this->username).md5($profile['create_time']));
    	   	    	setcookie("SYSTEM_SESSION_USERNAME",$this->username,time()+(int)$cookies,"/");
    	   	    	setcookie("SYSTEM_SESSION_PASSWORD",$password,time()+(int)$cookies,"/");
    	   	    }
    	   	    
    	   	    if($this->lastViewedPage!=""){
    	   	    	Openbizx::$app->getClientProxy()->ReDirectPage($this->lastViewedPage);
    	   	    }
       	        else{
       	        	Openbizx::$app->getClientProxy()->ReDirectPage($this->MOBILE_STARTPAGE);    	        	
       	        }       	        
    		    return true;
    		}
    		else
    		{ 
    			switch($this->auth_method)
    			{
    				case "smartcard":
    					$logComment=array($this->smartcard);
    					$eventlog->log("LOGIN", "MSG_SMARTCARD_LOGIN_FAILED", $logComment);    					
    					$errorMessage['smartcard'] = $this->getMessage("SMARTCARD_INCORRECT");
    					break;
    				default:
						$logComment=array($this->username,
    								$_SERVER['REMOTE_ADDR'],
    								$this->password);
    					$eventlog->log("LOGIN", "MSG_LOGIN_FAILED", $logComment);
    					$errorMessage['password'] = $this->getMessage("PASSWORD_INCORRECT");    					
    					break;
    			}
    			
    			$errorMessage['login_status'] = $this->getMessage("LOGIN_FAILED");    			    			
    			$this->processFormObjError($errorMessage);    			
    		}
    	}
    	catch (Exception $e) {    	
    		$errorMessage['login_status'] = $this->getMessage("LOGIN_FAILED");    			    			
    		$this->processFormObjError($errorMessage);    				
    	    //Openbizx::$app->getClientProxy()->showErrorMessage($e->getMessage());
    	}
    }

}  
