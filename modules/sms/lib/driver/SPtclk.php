<?php

use Openbizx\Openbizx;

require_once 'iSMS.php';
require_once 'SPDriver.php';
require_once dirname(dirname(__FILE__)).'/dll/emay/nusoaplib/nusoap.php';


class SPtclk extends SPDriver implements iSMS 
{
	protected $providerId = 5;
	protected $type = 'tclk';

	protected $url = "http://inolink.com/WS/linkWS.asmx/";
		
	public function activeService()
	{
		return true;
		$providerInfo = $this->_getProviderInfo();
		$CorpID = $providerInfo['username'];
		$Pwd = $providerInfo['password'];
		
		$CropName 	= urlencode(Openbizx::$app->getUserProfile("profile_company"));
		$LinkMan 	= urlencode(Openbizx::$app->getUserProfile("profile_display_name"));
		$Tel 		= urlencode(Openbizx::$app->getUserProfile("profile_phone"));
		$Mobile 	= urlencode(Openbizx::$app->getUserProfile("profile_mobile"));
		$Email		= urlencode(Openbizx::$app->getUserProfile("email"));
				
		//$url = $this->url."Reg?CorpID=$CorpID&Pwd=$Pwd&CorpName=$CorpName&LinkMan=$LinkMan&Tel=$Tel&Mobile=$Mobile&Email=$Email";
		//$result = file_get_contents($url);

		if(0 == $result)
		{
			return true;
		}
		else
		{
		    return false;
		}
		
	}
	
	public function send($mobile,$content,$schedule=null)
	{		

		$providerInfo  = $this->_getProviderInfo();
		$CorpID = $providerInfo['username'];
		$Pwd = $providerInfo['password'];
		
		if($schedule=="0000-00-00 00:00:00")
		{
			$schedule='';
		}else{
			$schedule = date('YmdHis',strtotime($schedule));
		}
		$mobile_log = $mobile;
		$content_log = $content;
		
		$mobile  = urlencode($mobile);
		$content = urlencode(iconv("UTF-8","GBK",$content));
		
		$url = $this->url."BatchSend?CorpID=$CorpID&Pwd=$Pwd&Mobile=$mobile&Content=$content&Cell=&SendTime=$schedule";
		$result = file_get_contents($url);	

		preg_match("/\">(.*?)<\/int/si", $result,$match);
		$result = (int)$match[1];
				
		if($result<0)
		{				
			Openbizx::getService(LOG_SERVICE)->log(LOG_ERR,"SMS","sendMessage: ". $content." TCLK：".$mobile.':'.$result['msg']);
			return false;
		}
		else
		{			
			$this->HitMessageCounter();
			$this->_log($mobile_log,$content_log,$schedule);	
			return true;
		}
			
	}

    public function getMsgBalance()
    {       
    	$providerInfo  = $this->_getProviderInfo();
		$CorpID = $providerInfo['username'];
		$Pwd = $providerInfo['password'];
		
		$url = $this->url."SelSum?CorpID=$CorpID&Pwd=$Pwd";
		$result = file_get_contents($url);
			
		preg_match("/\">(.*?)<\/int/si", $result,$match);
		$balance = (int)$match[1];
    	$this->updateMsgBalance($balance);
		return $balance;
	}
    	
  
}
