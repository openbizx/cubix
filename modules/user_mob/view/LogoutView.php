<?php
/**
 * Openbizx Cubi Application Platform
 *
 * LICENSE http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 *
 * @package   cubi.user.view
 * @copyright Copyright (c) 2005-2011, Openbiz Technology LLC
 * @license   http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 * @link      http://code.google.com/p/openbiz-cubi/
 * @version   $Id: LogoutView.php 3375 2012-05-31 06:23:11Z rockyswen@gmail.com $
 */

use Openbizx\Openbizx;
use Openbizx\Easy\WebPage;

class LogoutView extends WebPage
{
    public function __construct(&$xmlArr)
    {
        $this->Logout();
    }
    
    public function Logout()
    {
		$eventlog 	= Openbizx::getService(OPENBIZ_EVENTLOG_SERVICE);
		$profile = Openbizx::$app->getUserProfile();  
		$logComment=array($profile["username"], $_SERVER['REMOTE_ADDR']);
		
		$eventlog->log("LOGIN", "MSG_LOGOUT_SUCCESSFUL", $logComment);
		
		// destroy all data associated with current session:
		Openbizx::$app->getSessionContext()->destroy();
		
		//clean cookies
		setcookie("SYSTEM_SESSION_USERNAME",null,time()-100,"/");
    	setcookie("SYSTEM_SESSION_PASSWORD",null,time()-100,"/");
			
		// Redirect:
		//header("Location: ".OPENBIZ_APP_INDEX_URL."/user_mob/login");    	
		echo OPENBIZ_APP_INDEX_URL."/user_mob/login";
		exit;
    }
}
