<?php

use Openbizx\Openbizx;

require_once('_OAuth/oauth.php');
require_once "oauth.class.php";

class baiduapp extends oauthClass
{

    protected $type = 'baiduapp';
    protected $tokenUrl = 'https://openapi.baidu.com/oauth/2.0/token';
    protected $userUrl = 'https://openapi.baidu.com/rest/2.0/passport/users/getInfo';
    protected $authorizeUrl = 'https://openapi.baidu.com/oauth/2.0/authorize'; //登录验证地址
    protected $loginUrl;
    protected $userPass = 'www.openbiz.cn';
    private $akey;
    private $skey;
    private $suffix = '@baiduaccount';

    public function __construct()
    {
        parent::__construct();
        $recArr = $this->getProviderList();
        $this->akey = $recArr['key'];
        $this->skey = $recArr['value'];
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

        //请求参数
        $postfields = array('grant_type' => 'authorization_code',
            'client_id' => $this->akey,
            'client_secret' => $this->skey,
            'code' => $_REQUEST['code'],
            'redirect_uri' => $this->callBack
        );
        if (!$_REQUEST['code']) {
            exit('code为空');
        }
        $token = json_decode(OAuthUtil::Curl_Post($this->tokenUrl, $postfields), true);

        if ($token['access_token']) {
            $recinfo['oauth_token'] = $token['access_token'];
            $recinfo['oauth_token_secret'] = $token['session_secret'];
            $recinfo['access_token_json'] = $token;
            Openbizx::$app->getSessionContext()->setVar($this->type . '_access_token', $recinfo);
            $userInfo = $this->userInfo();
            $this->check($userInfo);
        } else {
            throw new Exception('验证非法！');
            return false;
        }
    }

    /* 获取登录页 */

    public function getUrl($call_back = null)
    {
        /* oauth.taobao.com/authorize?response_type=code&client_id
         * =12382619&redirect_uri=127.0.0.1/loginDemo/oauthLogin.php&state=1
         * http://taotools.bbaos.com/ws.php/oauth/callback/callback/type_alitao/app_TitleOptimization/
         */
        $this->loginUrl = $this->authorizeUrl . '?response_type=code&client_id=' . $this->akey . '&redirect_uri=' . $this->callBack . '&display=page';

        return $this->loginUrl;
    }

    //用户资料
    public function userInfo()
    {
        $recinfo = Openbizx::$app->getSessionContext()->getVar($this->type . '_access_token');
        $postfields = array('access_token' => $recinfo['oauth_token'], 'format' => 'json');

        $user = json_decode(OAuthUtil::Curl_Post($this->userUrl, $postfields), true);

        if (!$user) {
            return false;
        }
        $user['id'] = $user['userid'];
        $user['type'] = $this->type;
        $user['uname'] = $user['username'] . $this->suffix;
        return $user;
    }

    public function autoCreateUser()
    {
        return $this->CreateUser();
    }

}
