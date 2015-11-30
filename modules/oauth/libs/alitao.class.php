<?php

use Openbizx\Openbizx;

require_once('_OAuth/oauth.php');
require_once "oauth.class.php";
class alitao extends oauthClass
{
	protected $type='alitao'; 
	protected $tokenUrl='https://oauth.taobao.com/token';
	protected $authorizeUrl='https://oauth.taobao.com/authorize';//登录验证地址
	protected $loginUrl;
	private $akey;
	private $skey;
	
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
		//暂时没有发现如何验证合法性
        return $rec_arr['oauth_token']='ok';
	}
	
	function callback(){ 
		
		  //请求参数
		 $postfields= array('grant_type'     => 'authorization_code',
							 'client_id'     => $this->akey,
							 'client_secret' => $this->skey,
							 'code'          => $_REQUEST['code'],
							 'redirect_uri'  => $this->callBack
						);
		 $token = json_decode(OAuthUtil::Curl_Post($this->tokenUrl,$postfields),true); 
	
		if($token['access_token'])
		{ 	
			$recinfo['oauth_token']=$token['access_token'];
			$recinfo['oauth_token_secret']='';
			$recinfo['access_token_json']=$token;
			Openbizx::$app->getSessionContext()->setVar('alitao_access_token',$recinfo);
			$userInfo=$this->userInfo(); 
			$this->check($userInfo);
		}
		else
		{
			throw new Exception('验证非法！');
			return false;
		}
	}
    /*获取登录页*/
    function getUrl($call_back = null) {
		/*oauth.taobao.com/authorize?response_type=code&client_id
		 * =12382619&redirect_uri=127.0.0.1/loginDemo/oauthLogin.php&state=1
		 */
		$this->loginUrl=$this->authorizeUrl.'?response_type=code&client_id='.$this->akey.'&redirect_uri='.$this->callBack;
		return $this->loginUrl;
	}  

	//用户资料
	function userInfo(){
		$recinfo= Openbizx::$app->getSessionContext()->getVar('alitao_access_token');
	
		$user['id']          = $recinfo['access_token_json']['taobao_user_id'];
		$user['type']        = $this->type;
		$user['uname']       = urldecode($recinfo['access_token_json']['taobao_user_nick']);
		return $user;
	}

 
 
}
