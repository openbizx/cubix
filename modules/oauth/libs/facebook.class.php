<?php

use Openbizx\Openbizx;

require_once ('facebook/facebook.php');
require_once ("oauth.class.php");

class facebook extends oauthClass
{

    protected $type = 'facebook';
    protected $loginUrl;
    private $akey;
    private $skey;
    private $aliapy_config;
    private $facebook;

    public function __construct()
    {
        parent::__construct();
        $recArr = $this->getProviderList();
        $this->akey = $recArr['key'];
        $this->skey = $recArr['value'];
        $this->facebook = new FacebookApi(array(
            'appId' => $this->akey,
            'secret' => $this->skey,
            'CallBack' => $this->callBack,
        ));
    }

    function login()
    {
        $redirectPage = $this->getUrl();
        Openbizx::$app->getClientProxy()->ReDirectPage($redirectPage);
    }

    function test($akey, $skey)
    {
        //暂时没有发现如何验证合法性
        return $rec_arr['oauth_token'] = 'ok';
    }

    function callback()
    {
        $access_token['oauth_token'] = $this->facebook->getAccessToken();
        if (!$access_token) {
            throw new Exception('Unknown facebook AccessToken');
            return false;
        }
        $getSigned = $this->facebook->getSignedRequest();
        $access_token['access_token_json'] = $_GET;
        $access_token['oauth_token_secret'] = $_GET['code'];
        Openbizx::$app->getSessionContext()->setVar($this->type . '_access_token', $access_token);
        $userInfo = $this->userInfo();
        $this->check($userInfo);
    }

    /* 获取登录页 */

    function getUrl($call_back = null)
    {

        if (empty($this->akey) || empty($this->skey)) {
            throw new Exception('Unknown Facebook_akey');
            return false;
        }
        $this->loginUrl = $this->facebook->getLoginUrl();

        return $this->loginUrl;
    }

    //用户资料
    function userInfo()
    {
        $me = $this->facebook->api('/me');
        $user['id'] = $me['id'];
        $user['type'] = $this->type;
        $user['email'] = $me['data']['email'];
        $user['uname'] = $me['name'];
        $user['location'] = $me['locale'];
        $user['sex'] = ($me['gender'] == 'male') ? 1 : 0;
        $user['first_name'] = $me['first_name'];
        $user['last_name'] = $me['last_name'];
        $user['username'] = $me['username'];
        $user['verified'] = $me['verified'];
        $user['updated_time'] = $me['updated_time'];
        $user['userface'] = 'https://graph.facebook.com/' . $me['id'] . '/picture';
        return $user;
    }

}
