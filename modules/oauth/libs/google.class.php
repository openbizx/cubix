<?php

use Openbizx\Openbizx;

require_once "oauth.class.php";
require_once 'google/apiClient.php';
require_once 'google/contrib/apiOauth2Service.php';

class google extends oauthClass
{

    protected $type = 'google';
    protected $loginUrl;
    private $akey;
    private $skey;
    private $google;
    private $oauth2;

    public function __construct()
    {
        parent::__construct();
        $recArr = $this->getProviderList();
        $this->akey = $recArr['key'];
        $this->skey = $recArr['value'];
        $this->google = new apiClient();
        $this->google->setClientId($recArr['key']);
        $this->google->setClientSecret($recArr['value']);
        $this->google->setRedirectUri($this->callBack);
    }

    function login()
    {
        $redirectPage = $this->getUrl();
        Openbizx::$app->getClientProxy()->ReDirectPage($redirectPage);
    }

    function test($akey, $skey)
    {
        //暂时没有发现如何验证GOOGLE的Client ID合法性
        return $rec_arr['oauth_token'] = 'ok';
    }

    function callback()
    {
        $this->oauth2 = new apiOauth2Service($this->google);
        $this->google->authenticate();

        $access_token_json = $this->google->getAccessToken();

        $access_token = (array) json_decode($access_token_json);
        $access_token['oauth_token'] = $access_token['access_token'];
        $access_token['oauth_token_secret'] = $access_token['id_token'];
        $access_token['access_token_json'] = $access_token_json;
        Openbizx::$app->getSessionContext()->setVar('google_access_token', $access_token);

        $userInfo = $this->userInfo();
        $this->check($userInfo);
    }

    function logout()
    {
        Openbizx::$app->getSessionContext()->clearVar('google_access_token');
        $this->google->revokeToken();
    }

    /* 获取登录URL */

    function getUrl($call_back = null)
    {

        if (!$this->akey || !$this->skey) {
            throw new Exception('Unknown akey');
            return false;
        }
        $oauth2 = new apiOauth2Service($this->google);

        return $this->google->createAuthUrl();
    }

    //用户资料
    function userInfo()
    {
        $access_token = Openbizx::$app->getSessionContext()->getVar('google_access_token');
        $this->google->setAccessToken($access_token['access_token_json']);

        if (!$this->oauth2) {
            $this->oauth2 = new apiOauth2Service($this->google);
        }
        $me = $this->oauth2->userinfo->get();
        $user['id'] = $me['id'];
        $user['type'] = $this->type;
        $user['uname'] = $me['name'];
        $user['province'] = '';
        $user['city'] = '';
        $user['location'] = $me['locale'];
        $user['email'] = $me['email'];
        $user['userface'] = $me['picture'];
        $user['sex'] = ($me['gender'] == 'male') ? 1 : 0;
        return $user;
    }

    //验证用户
    function checkUser($oauth_token, $oauth_token_secret)
    {
        
    }

}
