<?php

use Openbizx\Openbizx;

require_once 'iSMS.php';
require_once 'SPDriver.php';
require_once dirname(dirname(__FILE__)).'/dll/emay/include/EmayClient.php';

class SPemay extends SPDriver implements iSMS 
{
	protected $providerId = 3;
	protected $type = 'emay';
	protected $websvcURL	=	'http://sdkhttp.eucp.b2m.cn/sdk/SDKService?wsdl';
	protected $clientObj;
	
	
	protected function getClientObj()
	{
		if($this->clientObj)
		{
			return $this->clientObj;
		}		
		$ProviderInfo = $this->_getProviderInfo(); 
		if(!$ProviderInfo)
		{
			return false;
		}
		$serialNumber = $ProviderInfo['username'];
		$password = $ProviderInfo['password'];
		$sessionKey = $ProviderInfo['username'];
		
		$connectTimeOut = 2;
		$readTimeOut = 10;
		
		$proxyhost = false;
		$proxyport = false;
		$proxyusername = false;
		$proxypassword = false; 
		
		$client = new EmayClient($this->websvcURL,$serialNumber,$password,$sessionKey,
							$proxyhost,$proxyport,$proxyusername,$proxypassword,$connectTimeOut,$readTimeOut);
		$client->setOutgoingEncoding("UTF-8");
		$this->clientObj = $client;
		return $this->clientObj;
	}
		
	public function activeService()
	{
		$ProviderInfo = $this->_getProviderInfo(); 
		if(!$ProviderInfo)
		{
			return false;
		}
		$result = $this->getClientObj()->login($ProviderInfo['username']);
		$this->getMsgBalance();
		return $result;
	}
	
	public function send($mobile,$content,$schedule=null)
	{		
		if($schedule=="0000-00-00 00:00:00")
		{
			$schedule='';
		}else{
			$schedule = date('YmdHis',strtotime($schedule));
		}

		$result = $this->getClientObj()->sendSMS(array($mobile),$content,$schedule,'','UTF-8');
		
		if($result)
		{				
			Openbizx::getService(LOG_SERVICE)->log(LOG_ERR,"SMS","sendMessage: ". $content." emay：".$mobile.':'.$result);
			return false;
		}
		else
		{
			$this->HitMessageCounter();
			$this->_log($mobile,$content,$schedule);	
			return true;
		}
			
	}

    public function getMsgBalance()
    {
    	$balance = $this->getClientObj()->getBalance();
    	$unitPrice = $this->getClientObj()->getEachFee();
    	$count = (int)($balance/$unitPrice);
    	$this->updateMsgBalance($count);
		return $count;
	}
    	
  
}
