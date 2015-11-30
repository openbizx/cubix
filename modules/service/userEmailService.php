<?php
/**
 * Openbizx Cubi Application Platform
 *
 * LICENSE http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 *
 * @package   cubi.service
 * @copyright Copyright (c) 2005-2011, Openbiz Technology LLC
 * @license   http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 * @link      http://code.google.com/p/openbiz-cubi/
 * @version   $Id: userEmailService.php 4380 2012-09-28 09:45:42Z hellojixian@gmail.com $
 */

use Openbizx\Openbizx;
use Openbizx\Object\MetaObject;
use Openbizx\Helpers\TemplateHelper;

/**
 * User email service 
 */
class userEmailService extends MetaObject
{
    public $tempaltes;
	public $emailDataObj;
	public $sendtoQueue;
    
    function __construct(&$xmlArr)
    {
        $this->readMetadata($xmlArr);
    } 
       	
    protected function readMetadata(&$xmlArr)
    {
        parent::readMetadata($xmlArr);
    	$this->tempaltes	 	= $this->readTemplates($xmlArr["PLUGINSERVICE"]["TEMPLATE"]);
        $this->emailDataObj 	= isset($xmlArr["PLUGINSERVICE"]["ATTRIBUTES"]["BIZDATAOBJ"]) ? $xmlArr["PLUGINSERVICE"]["ATTRIBUTES"]["BIZDATAOBJ"]: "email.do.EmailQueueDO";
        $this->sendtoQueue	= isset($xmlArr["PLUGINSERVICE"]["ATTRIBUTES"]["SENDTOQUEUE"]) ? $xmlArr["PLUGINSERVICE"]["ATTRIBUTES"]["SENDTOQUEUE"] : "Y";
    }

    protected function readTemplates($templateArray)
    {
    	$templates = array();
    	foreach($templateArray as $template){
    		$templates[$template['ATTRIBUTES']['NAME']] = $template['ATTRIBUTES'];
    	}
    	return $templates;
    }
    
    public function UserWelcomeEmail($userId)
	{
		//init email info
		$template = $this->tempaltes["WelcomeEmail"]["TEMPLATE"];
		$subject  = $this->tempaltes["WelcomeEmail"]["TITLE"];
		$sender   = $this->tempaltes["WelcomeEmail"]["EMAILACCOUNT"];
		
		//prepare data     
        $userDO = Openbizx::getObject("system.do.UserDO");
        $data = $userDO->directFetch("[Id]='".$userId."'", 1);

        if(!count($data))
        	return false;
        	        
        $userData = $data[0];
        $data 	  = array("userinfo"=>$userData);
        $data['app_index'] = OPENBIZ_APP_INDEX_URL;
		$data['app_url'] = OPENBIZ_APP_URL;
		$data['operator_name'] = Openbizx::$app->getProfile()->getProfileName(Openbizx::$app->getUserProfile("Id"));
		$data['refer_url'] = SITE_URL;
		       
		//render the email tempalte
		$tplFile = TemplateHelper::getTplFileWithPath($template, "email");
		$content = $this->renderEmail($data, $tplFile);

		//prepare recipient info
		$recipient['email'] = $userData['email'];
		$recipient['name']  = $userData['username'];
		
		//send it to the queue		
		$result = $this->sendEmail($sender,$recipient,$subject,$content);
		return $result;
	}
	
    public function resetUserPassword($tokenId) {
		//init email info
		$template = $this->tempaltes["ForgetPasswordEmail"]["TEMPLATE"];
		$subject  = $this->tempaltes["ForgetPasswordEmail"]["TITLE"];
		$sender   = $this->tempaltes["ForgetPasswordEmail"]["EMAILACCOUNT"];
		
		//prepare data
        /* @var $tokenDO BizDataObj */
		$tokenDO = Openbizx::getObject("system.do.UserPassTokenDO");
        $data = $tokenDO->directFetch("[Id]='".$tokenId."'", 1);
		if(!count($data))
        	return false;        
        $userId = $data[0]['user_id'];
		$data 	 = $data[0];
		$data['app_index'] = OPENBIZ_APP_INDEX_URL;
		$data['app_url'] = OPENBIZ_APP_URL;
		$data['operator_name'] = Openbizx::$app->getProfile()->getProfileName(Openbizx::$app->getUserProfile("Id"));
		$data['refer_url'] = SITE_URL;
		
        $userObj = Openbizx::getObject("system.do.UserDO");
        $userData = $userObj->directFetch("[Id]='".$userId."'", 1);                	        
        if(!count($data))
        	return false;
        $userData = $userData[0];
        
        $data = array(  "userinfo"=>$userData,
        				"token"=>$data	);
        
		//render the email tempalte
		$tplFile = TemplateHelper::getTplFileWithPath($template, "email");
		$content = $this->renderEmail($data, $tplFile);
		
		//prepare recipient info
		$recipient['email'] = $userData['email'];
		$recipient['name']  = $userData['username'];
		
		//send it to the queue		
		$result = $this->sendEmail($sender,$recipient,$subject,$content);
		return $result;		
        
    }
    
	public function UserResetPassword($tokenId)
	{
        return $this->resetUserPassword($tokenId);
	}

	public function DataSharingEmail($recipient_user_id, $data)
	{
		//init email info
		$template = $this->tempaltes["DataSharingEmail"]["TEMPLATE"];
		$subject  = $this->tempaltes["DataSharingEmail"]["TITLE"];
		$sender   = $this->tempaltes["DataSharingEmail"]["EMAILACCOUNT"];
				        
		//render the email tempalte
		$tplFile = TemplateHelper::getTplFileWithPath($template, "email");
		$content = $this->renderEmail($data, $tplFile);
		
		//prepare recipient info
		$userObj = Openbizx::getObject("system.do.UserDO");
        $userData = $userObj->directFetch("[Id]='".$recipient_user_id."'", 1);                	        
        if(!count($data))
        	return false;
        $userData = $userData[0];
        
		$recipient['email'] = $userData['email'];
		$recipient['name']  = $userData['username'];
		
		//send it to the queue		
		$result = $this->sendEmail($sender,$recipient,$subject,$content);
		return $result;		
	}	
	
	public function TaskUpdateEmail($recipient_user_id, $data)
	{
		//init email info
		$template = $this->tempaltes["TaskUpdateEmail"]["TEMPLATE"];
		$subject  = $this->tempaltes["TaskUpdateEmail"]["TITLE"];
		$sender   = $this->tempaltes["TaskUpdateEmail"]["EMAILACCOUNT"];
				        
		//render the email tempalte
		$tplFile = TemplateHelper::getTplFileWithPath($template, "email");
		$content = $this->renderEmail($data, $tplFile);
		
		//prepare recipient info
		$userObj = Openbizx::getObject("system.do.UserDO");
        $userData = $userObj->directFetch("[Id]='".$recipient_user_id."'", 1);                	        
        if(!count($data))
        	return false;
        $userData = $userData[0];
        
		$recipient['email'] = $userData['email'];
		$recipient['name']  = $userData['username'];
		
		//send it to the queue		
		$result = $this->sendEmail($sender,$recipient,$subject,$content);
		return $result;		
	}

	public function NewMessageEmail($recipient_user_id, $data)
	{
		//init email info
		$template = $this->tempaltes["NewMessageEmail"]["TEMPLATE"];
		$subject  = $this->tempaltes["NewMessageEmail"]["TITLE"];
		$sender   = $this->tempaltes["NewMessageEmail"]["EMAILACCOUNT"];
				        
		//render the email tempalte
		$tplFile = TemplateHelper::getTplFileWithPath($template, "email");
		$content = $this->renderEmail($data, $tplFile);
		
		//prepare recipient info
		$userObj = Openbizx::getObject("system.do.UserDO");
        $userData = $userObj->directFetch("[Id]='".$recipient_user_id."'", 1);                	        
        if(!count($data))
        	return false;
        $userData = $userData[0];
        
		$recipient['email'] = $userData['email'];
		$recipient['name']  = $userData['username'];
		
		//send it to the queue		
		$result = $this->sendEmail($sender,$recipient,$subject,$content);
		return $result;		
	}	
	
	public function DataAssignedEmail($recipient_user_id, $data)
	{
		//init email info
		$template = $this->tempaltes["DataAssignedEmail"]["TEMPLATE"];
		$subject  = $this->tempaltes["DataAssignedEmail"]["TITLE"];
		$sender   = $this->tempaltes["DataAssignedEmail"]["EMAILACCOUNT"];
				        
		//render the email tempalte
		$tplFile = TemplateHelper::getTplFileWithPath($template, "email");
		$content = $this->renderEmail($data, $tplFile);
		
		//prepare recipient info
		$userObj = Openbizx::getObject("system.do.UserDO");
        $userData = $userObj->directFetch("[Id]='".$recipient_user_id."'", 1);                	        
        if(!count($data))
        	return false;
        $userData = $userData[0];
        
		$recipient['email'] = $userData['email'];
		$recipient['name']  = $userData['username'];
		
		//send it to the queue		
		$result = $this->sendEmail($sender,$recipient,$subject,$content);
		return $result;		
	}		
	
	public function DataPublishEmail($recipient_user_id, $data)
	{
		//init email info
		$template = $this->tempaltes["DataPublishEmail"]["TEMPLATE"];
		$subject  = $this->tempaltes["DataPublishEmail"]["TITLE"];
		$sender   = $this->tempaltes["DataPublishEmail"]["EMAILACCOUNT"];
				        
		//render the email tempalte
		$tplFile = TemplateHelper::getTplFileWithPath($template, "email");
		$content = $this->renderEmail($data, $tplFile);
		
		//prepare recipient info
		$userObj = Openbizx::getObject("system.do.UserDO");
        $userData = $userObj->directFetch("[Id]='".$recipient_user_id."'", 1);                	        
        if(!count($data))
        	return false;
        $userData = $userData[0];
        
		$recipient['email'] = $userData['email'];
		$recipient['name']  = $userData['username'];
		
		//send it to the queue		
		$result = $this->sendEmail($sender,$recipient,$subject,$content);
		return $result;		
	}		
	
	public function SendEmailToUser($template_name, $recipient_user_id, $data)
	{
		//init email info
		$template = $this->tempaltes[$template_name]["TEMPLATE"];
		$subject  = $this->tempaltes[$template_name]["TITLE"];
		$sender   = $this->tempaltes[$template_name]["EMAILACCOUNT"];
				        
		//render the email tempalte		
		$data['app_index'] = OPENBIZ_APP_INDEX_URL;
		$data['app_url'] = OPENBIZ_APP_URL;
		$data['operator_name'] = Openbizx::$app->getProfile()->getProfileName(Openbizx::$app->getUserProfile("Id"));
		$data['refer_url'] = SITE_URL;
		
		$tplFile = TemplateHelper::getTplFileWithPath($template, "email");
		$content = $this->renderEmail($data, $tplFile);
		
		//prepare recipient info
		$userObj = Openbizx::getObject("system.do.UserDO");
        $userData = $userObj->directFetch("[Id]='".$recipient_user_id."'", 1);                	        
        if(!count($data))
        	return false;
        $userData = $userData[0];
        
		$recipient['email'] = $userData['email'];
		$recipient['name']  = $userData['username'];
		
		//send it to the queue		
		$result = $this->sendEmail($sender,$recipient,$subject,$content);
		return $result;		
	}
	
	public function SendEmailToContact($template_name, $recipient_contact_id, $data)
	{
		//init email info
		$template = $this->tempaltes[$template_name]["TEMPLATE"];
		$subject  = $this->tempaltes[$template_name]["TITLE"];
		$sender   = $this->tempaltes[$template_name]["EMAILACCOUNT"];
				        
		//render the email tempalte	
		$data['app_index'] = OPENBIZ_APP_INDEX_URL;
		$data['app_url'] = OPENBIZ_APP_URL;

		
		$data['operator_name'] = Openbizx::$app->getProfile()->getProfileName($data['create_by']);
		$data['operator_email'] = Openbizx::$app->getProfile()->getProfileEmail($data['create_by']);
		$data['refer_url'] = SITE_URL;
		
		//prepare recipient info
		$userObj = Openbizx::getObject("contact.do.ContactSystemDO");
        $userData = $userObj->directFetch("[Id]='".$recipient_contact_id."'", 1);                	        
        if(!count($data))
        	return false;
        $userData = $userData[0];
        
		$recipient['email'] = $userData['email'];
		$recipient['name']  = $userData['display_name'];
		
		
		$data['contact_display_name'] = $userData['display_name'];
		
		
		$tplFile = TemplateHelper::getTplFileWithPath($template, "email");
		$content = $this->renderEmail($data, $tplFile);
		if($userData['email']==''){
			//if no email address , then do nothing
			return ;
		}
		
		//send it to the queue		
		$result = $this->sendEmail($sender,$recipient,$subject,$content);
		return $result;		
	}
	
	public function SystemInternalErrorEmail($recipient, $errMsg)
	{
		//init email info
		$template = $this->tempaltes["SystemInternalError"]["TEMPLATE"];
		$subject  = $this->tempaltes["SystemInternalError"]["TITLE"];
		$sender   = $this->tempaltes["SystemInternalError"]["EMAILACCOUNT"];
		
		//prepare data
		$system 	=  array("error_message"=>$errMsg);
		$data		=  array("system"=>$system);
        
		//render the email tempalte
		$tplFile = TemplateHelper::getTplFileWithPath($template, "email");
		$content = $this->renderEmail($data, $tplFile);
				
		//send it to the queue		
		$result = $this->sendEmail($sender,$recipient,$subject,$content);
		return $result;		
	}
	
	public function CronJobEmail($recipientEmail, $job_name, $output)
	{
		//init email info
		$template = $this->tempaltes["CronjobEmail"]["TEMPLATE"];
		$subject  = $this->tempaltes["CronjobEmail"]["TITLE"];
		$sender   = $this->tempaltes["CronjobEmail"]["EMAILACCOUNT"];
		
		//prepare data
		$data["job_name"] = $job_name;
		$data["job_output"] = $output;
        
		//render the email tempalte
		$tplFile = TemplateHelper::getTplFileWithPath($template, "email");
		$content = $this->renderEmail($data, $tplFile);
		
		//prepare recipient info
		$recipient['email'] = $recipientEmail;
		$recipient['name']  = $recipientEmail;
				
		//send it to the queue		
		$result = $this->sendEmail($sender,$recipient,$subject,$content);
		return $result;		
	}
	
	protected function renderEmail($content, $tplFile)
	{
        $smarty  = TemplateHelper::getSmartyTemplate();
        foreach ($content as $key=>$value){
        	$smarty->assign($key, $value);
        }
        return $smarty->fetch($tplFile);		
	}
	
	protected function sendEmail($sender,$recipient,$subject,$content)
	{		

		$dataObj = Openbizx::getObject($this->emailDataObj);
		
		if(is_array($recipient)){
			$recipient_name = $recipient['name'];
			$recipient		= $recipient['email'];
		}else{
			$recipient_name = "";
		}
		
		$recArr['sender'] 			= $sender;
	    $recArr['recipient_name'] 	= $recipient_name;
	    $recArr['recipient'] 		= $recipient;
	    $recArr['subject'] 			= $subject;
	    $recArr['content'] 			= $content;		    
	    
	    $ok = $dataObj->insertRecord($recArr);
		    
		if($this->sendtoQueue=='Y')
		{	
			return $ok;
		}
		else
		{			
			//send email now
			$recArr = $dataObj->getActiveRecord();
			$email_id = $recArr['Id'];
			$this->sendEmailNow($email_id);			
		}
		
	}
	
//	this function should be called by cronjob.php 
//	or called by SendEmail
	public function sendEmailNow($email_id){
		//prepare email data				
		$dataObj = Openbizx::getObject($this->emailDataObj);				
		$dataObj->setSearchRule("[Id]='".$email_id."' and [status]!='sending' ", true);	
		$data = $dataObj->fetch();
		$dataObj->setActiveRecord($data[0]);	
		if(!count($data))
        	return false;        
		$data 	 = $data[0];
		
		$sender = $data["sender"];
		$recipient = array(
					 array("email"=>$data["recipient"],
						   "name" =>$data["recipient_name"])
					 );
		$subject = $data["subject"];
		$content = $data["content"];							
		
		//update queue status to sending
		$recArr = array("status"=>"sending");
		$dataObj->updateRecord($recArr);
		
		//init email service
		$emailObj 	= Openbizx::getService(EMAIL_SERVICE);
		$emailObj->useAccount($sender);
	    $emailObj->sendEmail ($recipient, null,null, $subject, $content, null, true);

		//update queue status to sent
		$recArr = array("status"=>"sent");
		$dataObj->updateRecord($recArr);
	    return;
	}
	
	public function sendEmailFromQueue()
	{
		$dataObj = Openbizx::getObject($this->emailDataObj);
		$dataObj->setSortRule("[Id] ASC");
		$dataObj->setSearchRule("[status]='pending'", true);
		$data = $dataObj->fetch();
		
		foreach($data as $email){
			$this->sendEmailNow($email['Id']);
		}
		return ;
	}
}

