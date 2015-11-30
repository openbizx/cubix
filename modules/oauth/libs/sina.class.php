<?php

use Openbizx\Openbizx;

include_once('_OAuth/oauth.php');
require_once "oauth.class.php";
include_once( 'sina/saetv2.ex.class.php' );
class sina extends oauthClass
{
	protected $type='sina';
	protected $loginUrl;
	private $sina_akey;
	private $sina_skey;
	private $sina;
 
		
	public function __construct() {
		parent::__construct();
		$recArr=$this->getProviderList(); 
		$this->sina_akey = $recArr['key'];
		$this->sina_skey =$recArr['value']; 
		$this->sina = new SaeTOAuthV2( $this->sina_akey , $this->sina_skey);
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
		$keys = array();
		$keys['code'] = $_REQUEST['code'];
		$keys['redirect_uri'] = $this->callBack;
		$token = $this->sina->getAccessToken('code', $keys ) ; 
		if(!$token )
		{
			throw new Exception('Unknown sina_access_token');
			return false;
		}
		$token['oauth_token']=$token['access_token'] ; 
		$token['access_token_json']=$token; 
		Openbizx::$app->getSessionContext()->setVar('sina_access_token',$token);
		$userInfo=$this->userInfo($token['access_token']); 
		$this->check($userInfo);
	}
    /*获取登录页*/
    function getUrl($call_back = null) {
		if ( empty($this->sina_akey) || empty($this->sina_skey) )
		{
			throw new Exception('Unknown sina_akey');
			return false;
		}
		$this->loginUrl = $this->sina->getAuthorizeURL($this->callBack);
		return $this->loginUrl;
	} 
 
	//用户资料
	function userInfo($token){
		$c = new SaeTClientV2( $this->sina_akey , $this->sina_skey, $token);
		
		$home  = $c->home_timeline(); // done
		$uid_get = $c->get_uid(); 
		$uid = $uid_get['uid'];
		$me = $c->show_user_by_id( $uid);//根据ID获取用户等基本信息
	 
		$user['id']          = $me['id'];
		$user['type']        = $this->type;
		$user['uname']       = $me['name'];
		$user['province']    = $me['province'];
		$user['city']        = $me['city'];
		$user['location']    = $me['location'];
		$user['email']         =  $me['data']['email'];
		$user['userface']    = str_replace(  $user['id'].'/50/' , $user['id'].'/180/' ,$me['profile_image_url'] );
		$user['sex']         = ($me['gender']=='m')?1:0; 
		return $user;
	}

	 
 
}
