<?php

use Openbizx\Openbizx;

include_once('_OAuth/oauth.php');
require_once ('twitter/twitteroauth.php');
require_once ("oauth.class.php");
class twitter extends oauthClass
{
	protected $type='twitter';
	protected $loginUrl;
	private $akey;
	private $skey;
	private $aliapy_config;
	private $twitter;
 
 
		
	public function __construct() {
		parent::__construct();
		$recArr=$this->getProviderList(); 
		$this->akey = $recArr['key'];
		$this->skey =$recArr['value']; 
		
	}
	
  	function login(){	
		$redirectPage=$this->getUrl();
		Openbizx::$app->getClientProxy()->ReDirectPage($redirectPage);
	} 
	
	function test($akey,$skey){
		$Twitter = new TwitterOAuth($akey ,$skey);
        return $Twitter->getRequestToken($this->callBack);
	}
	
	function callback(){ 
		$oauth_token= Openbizx::$app->getSessionContext()->getVar('twitter_access_token');
		$Twitter = new TwitterOAuth($this->akey ,$this->skey,$oauth_token['oauth_token'], $oauth_token['oauth_token_secret']);
		$access_token = $Twitter->getAccessToken($_REQUEST['oauth_verifier']);
		if(!$access_token)
		{
			throw new Exception('Unknown facebook AccessToken');
			return false;
		}
		
		Openbizx::$app->getSessionContext()->setVar($this->type.'_access_token',$access_token);
	
		$userInfo=$this->userInfo($access_token['oauth_token'],$access_token['oauth_token_secret']);
		$this->check($userInfo);
	}
    /*获取登录页*/
    function getUrl($call_back = null) {
		
		if ( empty($this->akey) || empty($this->skey) )
		{
			throw new Exception('Unknown Twitter_akey');
			return false;
		}
		$Twitter = new TwitterOAuth($this->akey ,$this->skey);
		$request_token = $Twitter->getRequestToken($this->callBack);
		
		if ($Twitter->http_code==200) 
		{
			/* Build authorize URL and redirect user to Twitter. */
			
			Openbizx::$app->getSessionContext()->setVar('twitter_access_token',$request_token); 
			$this->loginUrl = $Twitter->getAuthorizeURL($request_token);
		}
		else
		{
			throw new Exception('Could not connect to Twitter. Refresh the page or try again later.');
			return false;
		}
		return $this->loginUrl;
		} 

	//用户资料
	function userInfo($oauth_token=null,$oauth_token_secret=null){
		$Twitter = new TwitterOAuth($this->akey ,$this->skey,$oauth_token, $oauth_token_secret);
		$me =(array)$Twitter->get('account/verify_credentials');
		$user['id']         = $me['id'];
		$user['type']         = $this->type;
		$user['email']         = '';
		$user['uname']       = $me['name'];
		$user['location']    = $me['location'];
		$user['userface']  = $me['profile_image_url'];	
		return $user;
	}
 
}
