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
 * @version   $Id: oauthService.php 3371 2012-05-31 06:17:21Z rockyswen@gmail.com $
 */
use Openbizx\Openbizx;
use Openbizx\Easy\EasyForm;

class oauthClass extends EasyForm
{

    /**
     * 
     * OAuth type 
     * e.g.: Taobao or Facebook etc..
     * @var string
     */
    protected $type;

    /**
     * 
     * Temperary cache provider data
     * @var array
     */
    protected $providerData;

    /**
     * 
     * Data Object for storage users oauth token info
     * @var string
     */
    protected $userOAuthDO;
    protected $providers;
    protected $callBack;
    protected $oauthProviderDo = 'oauth.do.OauthProviderDO';

    public function __construct()
    {
        $this->callBack = SITE_URL . 'ws.php/oauth/callback/callback/type_' . $this->type . '/';
    }

    /**
     * 
     * Get OAuth provider data including api_key, api_secret, url etc
     * @return array;
     */
    public function getProviderList()
    {
        $recArr = Openbizx::$app->getSessionContext()->getVar("_OAUTH_{$this->type}");
        $recArr = false;
        if (!$recArr) {
            $do = Openbizx::getObject($this->oauthProviderDo);
            $recArr = $do->fetchOne("[status]=1 and [type]='{$this->type}'", 1);
            if ($recArr) {
                $recArr = $recArr->toArray();
            }
            Openbizx::$app->getSessionContext()->setVar("_OAUTH_{$this->type}", $recArr);
        }
        $recArr['key'] = trim($recArr['key']);
        $recArr['value'] = trim($recArr['value']);
        return $recArr;
    }

    /**
     * 
     * abstract functions need to be implement in sub class
     * Validate if the oauth info still available 
     * @param intger $user_id
     * @param intger $oauth_id
     * @return bool
     */
    public function validateUserOAuth($user_id, $oauth_id)
    {
        
    }

    /**
     * 
     * avstract function to check given oauth_data is valid or not
     * @param array oauth_data
     * @return bool
     */
    public function check($oauth_data)
    {

        if (!$oauth_data['id']) {
            throw new Exception('Unknown oauth_token');
            return;
        }

        $UserTokenObj = Openbizx::getObject('oauth.do.UserTokenDO');
        $UserToken = $UserTokenObj->fetchOne("[oauth_uid]='" . $oauth_data['id'] . "'");
        $access_token = Openbizx::$app->getSessionContext()->getVar($this->type . '_access_token');
        $oauth_data['oauth_token'] = $access_token['oauth_token'];
        $oauth_data['oauth_token_secret'] = $access_token['oauth_token_secret'];
        $oauth_data['access_token_json'] = $access_token['access_token_json'];

        Openbizx::$app->getSessionContext()->setVar('_OauthUserInfo', $oauth_data);

        if ($UserToken) {
            $UserOAuthArr['oauth_token'] = $oauth_data['oauth_token'];
            $UserOAuthArr['oauth_token_secret'] = $oauth_data['oauth_token_secret'];
            $UserOAuthArr['oauth_rawdata'] = serialize($oauth_data['access_token_json']);
            $UserOAuthArr['oauth_user_info'] = serialize($oauth_data);
            // $dataRec = new DataRecord($UserOAuthArr, $UserTokenObj);
            // $dataRec->id =$UserToken['Id'];
            //$dataRec->save( ); 
            $eventlog = Openbizx::getService(OPENBIZ_EVENTLOG_SERVICE);
            $logComment = array($userinfo['username'], $_SERVER['REMOTE_ADDR']);
            $eventlog->log("LOGIN", "MSG_LOGIN_SUCCESSFUL", $logComment);


            $UserTokenObj->updateRecords($UserOAuthArr, "[Id]={$UserToken['Id']}");
            $userObj = Openbizx::getObject('system.do.UserDO');
            $userinfo = $userObj->fetchOne("[Id]='" . $UserToken['user_id'] . "'");
            if ($userinfo) {

                $username = $userinfo['username'];
                $userinfo['lastlogin'] = date("Y-m-d H:i:s");
                $userinfo->save();
                $this->RunJumpPage($username);
            } else {
                //found a isolate oauth account
                $UserTokenObj->deleteRecords("[Id]={$UserToken['Id']}");
                Openbizx::$app->getClientProxy()->ReDirectPage(OPENBIZ_APP_INDEX_URL . '/user/logout');
            }
        } elseif (method_exists($this, 'autoCreateUser')) {
            return $this->autoCreateUser();
        } else {
            //未找到用户，跳转到注册页

            $assocURL = Openbizx::$app->getSessionContext()->getVar("oauth_assoc_url");
            if ($assocURL) {
                header("Location: " . $assocURL);
            } else {

                header("Location: " . OPENBIZ_APP_INDEX_URL . "/oauth/connect_user");
            }
        }
        return $profile;
    }

    public function RunJumpPage($username)
    {
        $profile = Openbizx::$app->InituserProfile($username);
        //获取当前用户角色的默认页
        $index = $profile['roles'][0];
        $roleStartpage = $rec_info['roleStartpage'][$index];
        $redirectPage = OPENBIZ_APP_INDEX_URL . $roleStartpage;
        $redirectURL = Openbizx::$app->getSessionContext()->getVar("oauth_redirect_url");
        if ($redirectURL) {
            $redirectPage = $redirectURL;
        }

        Openbizx::$app->getClientProxy()->ReDirectPage($redirectPage);
    }

    public function saveUserOAuth($user_id, $OauthUserInfo)
    {
        if (!$user_id || !$OauthUserInfo) {
            throw new Exception('Unknown UserInfo');
            return;
        }

        $UserTokenObj = Openbizx::getObject('oauth.do.UserTokenDO');
        $UserTokenArr = array(
            "user_id" => $user_id,
            "oauth_uid" => $OauthUserInfo['id'],
            "oauth_class" => $OauthUserInfo['type'],
            "oauth_token" => $OauthUserInfo['oauth_token'],
            "oauth_token_secret" => $OauthUserInfo['oauth_token_secret'],
            "oauth_rawdata" => serialize($OauthUserInfo['access_token_json']),
            "oauth_user_info" => serialize($OauthUserInfo),
            "create_by" => $user_id,
            "create_time" => date("Y-m-d H:i:s")
        );
        $return = false;
        if ($UserTokenObj->insertRecord($UserTokenArr)) {
            $return = true;
        }
        return $return;
    }

    public function CreateUser()
    {
        $userObj = Openbizx::getObject('system.do.UserDO');
        $oauth_data = Openbizx::$app->getSessionContext()->getVar('_OauthUserInfo');

        $recArr['username'] = $oauth_data['uname'];
        $recArr['password'] = hash('sha1', $this->userPass);
        $recArr['create_by'] = "0";
        $recArr['update_by'] = "0";
        $user_id = $userObj->insertRecord($recArr);

        //set default user role to member
        $userinfo = $userObj->getActiveRecord();

        $RoleDOName = "system.do.RoleDO";
        $UserRoleDOName = "system.do.UserRoleDO";

        $roleDo = Openbizx::getObject($RoleDOName, 1);
        $userRoleDo = Openbizx::getObject($UserRoleDOName, 1);

        $roleDo->setSearchRule("[default]=1");
        $defaultRoles = $roleDo->fetch();
        foreach ($defaultRoles as $role) {
            $role_id = $role['Id'];
            $userRoleArr = array(
                "user_id" => $userinfo['Id'],
                "role_id" => $role_id
            );
            $userRoleDo->insertRecord($userRoleArr);
        }

        //assign a default group to new user
        $GroupDOName = "system.do.GroupDO";
        $UserGroupDOName = "system.do.UserGroupDO";

        $groupDo = Openbizx::getObject($GroupDOName, 1);
        $userGroupDo = Openbizx::getObject($UserGroupDOName, 1);

        $groupDo->setSearchRule("[default]=1");
        $defaultGroups = $groupDo->fetch();
        foreach ($defaultGroups as $group) {
            $group_id = $group['Id'];
            $userGroupArr = array(
                "user_id" => $userinfo['Id'],
                "group_id" => $group_id
            );
            $userGroupDo->insertRecord($userGroupArr);
        }

        $userRoleObj = Openbizx::getObject('system.do.UserRoleDO');
        $uesrRoloArr = array(
            "user_id" => $userinfo['Id'],
            "role_id" => "2", //role 2 is Member
        );
        $userRoleObj->insertRecord($uesrRoloArr);
        //record event log   

        $eventlog = Openbizx::getService(OPENBIZ_EVENTLOG_SERVICE);
        $logComment = array($userinfo['username'], $_SERVER['REMOTE_ADDR']);
        $eventlog->log("USER_MANAGEMENT", "MSG_USER_REGISTERED", $logComment);
        //init profile for future use like redirect to my account view
        //$profile_id = Openbizx::getService(PROFILE_SERVICE)->CreateProfile($userinfo['Id']);
        //send user email
        $emailObj = Openbizx::getService(CUBI_USER_EMAIL_SERVICE);
        $emailObj->UserWelcomeEmail($userinfo['Id']);

        if ($userinfo['Id']) {
            $this->saveUserOAuth($userinfo['Id'], $oauth_data);
        }
        if (Openbizx::$app->InituserProfile($userinfo['username'])) {
            $this->RunJumpPage($userinfo['username']);
        } else {
            return false;
        }
    }

    public function clearUserOAuth($user_id, $oauth_id)
    {
        
    }

    public function getUserOAuthList($user_id)
    {
        
    }

    /*
      This method will used for redirect to 3rd party platform login page
     */

    public function login()
    {
        
    }

    public function getUserOauth()
    {
        
    }

    public function getUserInfo()
    {
        
    }

}
