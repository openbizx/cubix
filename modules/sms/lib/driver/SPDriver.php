<?php

use Openbizx\Openbizx;

require_once 'iSMS.php';
//SP = Service Provider 18dx

class SPDriver implements iSMS 
{
	protected $providerId;
	protected $providerDo = 'sms.provider.do.ProviderDO';
	protected $logDo	 	= 'sms.log.do.LogDO';		
	
	public function activeService(){}
	
	public function send($mobile,$content,$schedule=null){}
	
	public function getMsgBalance(){}
	
	public function updateMsgBalance($balance)
	{
		$providerRec= Openbizx::getObject($this->providerDo)->fetchOne("[Id]={$this->providerId}");
		$providerRec['msg_balance']=(int)$balance;
		$providerRec->save();
		return $this;
	}	
	
	public function HitMessageCounter()
	{		
		$providerRec= Openbizx::getObject($this->providerDo)->fetchOne("[Id]={$this->providerId}");
		$providerRec['msg_sent_count']=(int)$providerRec['msg_sent_count']+1;
		$providerRec['msg_last_sendtime']=date("Y-m-d H:i:s");
		$providerRec->save();
		$this->getMsgBalance();
		return $this;
	}
	
	public function getMessageCounter()
	{
		$providerRec= Openbizx::getObject($this->providerDo)->fetchOne("[Id]={$this->providerId}");		
		return $providerRec['msg_send_counter'];
	}
	
    protected  function _Log($mobile,$content,$schedule=null)
    {
    	$record = array(
    		"provider_id" 	=> $this->providerId,
    		"mobile" 		=> $mobile,
    		"content" 		=> $content,
    		"schedule"		=> $schedule,
    		"sent_time"		=> date("Y-m-d H:i:s")
    	);
    	return Openbizx::getObject($this->logDo)->insertRecord($record);
    }
	
	
	protected  function _getProviderInfo()
	{
		$SmsProviderDO = Openbizx::getObject($this->providerDo);
		$recObj=$SmsProviderDO->fetchOne("[Id]={$this->providerId}");
		$recArr=array();
		if($recObj)
		{
			$recArr=$recObj->toArray();
		}
		return $recArr;
	}	
	
}