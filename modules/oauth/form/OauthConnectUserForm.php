<?php

use Openbizx\Openbizx;

include_once(Openbizx::$app->getModulePath() . "/user/form/RegisterForm.php");

class OauthConnectUserForm extends RegisterForm
{

    protected $username;
    protected $password;
    protected $openRegisterStatus;

    public function CreateUser()
    {
        $OauthUserInfo = Openbizx::$app->getSessionContext()->getVar('_OauthUserInfo');
        if (!$OauthUserInfo) {
            throw new Exception('Unknown OauthUserInfo');
            return;
        }
        $userObj = Openbizx::getObject('oauth.do.UserTokenDO');
        $OauthUser = $userObj->fetchOne("[oauth_uid]='" . $OauthUserInfo['id'] . "'");
        if (!$OauthUser) {
            $userinfo = parent::_doCreateUser();
            //第三方登录用户关联帐号
            if ($userinfo['Id']) {
                include_once(Openbizx::$app->getModulePath() . "/oauth/libs/oauth.class.php");
                $OauthObj = new oauthClass();
                if (!$OauthObj->saveUserOAuth($userinfo['Id'], $OauthUserInfo)) {
                    $errorMessage = $this->GetMessage("ASSOCIATED_USER_FAILS");
                    $errors['fld_UserOAuth'] = $errorMessage;
                    $this->processFormObjError($errors);
                }
            }
        }
        $this->processPostAction();
    }

    public function ConnectUser()
    {
        // get the username and password	
        $this->username = Openbizx::$app->getClientProxy()->getFormInputs("fld_username");
        $this->password = Openbizx::$app->getClientProxy()->getFormInputs("fld_password");
        $eventlog = Openbizx::getService(OPENBIZ_EVENTLOG_SERVICE);

        try {
            if ($this->authUser()) {
                // after authenticate user: 1. init profile
                $profile = Openbizx::$app->InitUserProfile($this->username);
                $OauthUserInfo = Openbizx::$app->getSessionContext()->getVar('_OauthUserInfo');
                if (!$OauthUserInfo || !$profile['Id']) {
                    $this->errors = array($this->getMessage("TEST_FAILURE"));
                    $this->updateForm();
                    return false;
                }

                include_once(Openbizx::$app->getModulePath() . "/oauth/libs/oauth.class.php");
                $OauthObj = new oauthClass();
                if (!$OauthObj->saveUserOAuth($profile['Id'], $OauthUserInfo)) {
                    $this->errors = array("fld_password" => $this->getMessage("ASSOCIATED_USER_FAILS"));
                    $this->updateForm();
                    return false;
                } else {
                    //Openbizx::$app->getClientProxy()->showClientAlert($this->getMessage("ASSOCIATED_USER_SUCCESS"));
                }
                $this->switchForm("oauth.form.OauthConnectUserFinishedForm");
                /*
                  $redirectPage = OPENBIZ_APP_INDEX_URL.$profile['roleStartpage'][0];
                  if(!$profile['roleStartpage'][0])
                  {
                  Openbizx::$app->getClientProxy()->showClientAlert($this->getMessage("TEST_FAILURE"));
                  return false;
                  }

                  if($profile['roleStartpage'][0]){
                  Openbizx::$app->getClientProxy()->ReDirectPage($redirectPage);
                  }else{
                  parent::processPostAction();
                  }
                 */
                return true;
            } else {
                $logComment = array($this->username,
                    $_SERVER['REMOTE_ADDR'],
                    $this->password);
                $eventlog->log("LOGIN", "ASSOCIATED_LOGIN_FAILED", $logComment);
                $this->errors = array(
                    "fld_username" => $this->getMessage("ASSOCIATED_USER_FAILS"),
                    "fld_password" => " ");
                $this->updateForm();
                return false;
            }
        } catch (Exception $e) {
            Openbizx::$app->getClientProxy()->showClientAlert($e->getMessage());
        }
    }

    public function render()
    {
        $oauth_data = Openbizx::$app->getSessionContext()->getVar('_OauthUserInfo');

        if (!$oauth_data) {
            header("Location: " . OPENBIZ_APP_INDEX_URL . "/user/login");
            exit;
        }

        return parent::render();
    }

    public function fetchData()
    {
        //fill in open register status
        $do = Openbizx::getObject("myaccount.do.PreferenceDO");
        $rs = $do->fetchOne("[user_id]='0' AND [name]='open_register'");
        if (!$rs || $rs['value'] == 0) {
            $this->openRegisterStatus = 0;
        } else {
            $this->openRegisterStatus = 1;
        }

        if ($this->activeRecord != null) {
            $oauth_data = Openbizx::$app->getSessionContext()->getVar('_OauthUserInfo');
            $this->activeRecord['oauth_data'] = $oauth_data;
            $this->activeRecord['oauth_user'] = $oauth_data['uname'];
            $this->activeRecord['oauth_location'] = $oauth_data['location'];
            return $this->activeRecord;
        }

        if (strtoupper($this->formType) == "NEW")
            return $this->getNewRecord();

        //$record =  parent::fetchData();
        $oauth_data = Openbizx::$app->getSessionContext()->getVar('_OauthUserInfo');
        $record['oauth_data'] = $oauth_data;
        $record['oauth_user'] = $oauth_data['uname'];
        $record['oauth_location'] = $oauth_data['location'];

        $this->activeRecord = $record;
        return $record;
    }

    public function getNewRecord()
    {
        $oauth_data = Openbizx::$app->getSessionContext()->getVar('_OauthUserInfo');
        $record = array(
            "username" => $oauth_data['uname'],
            "email" => $oauth_data['email']
        );
        $record['oauth_data'] = $oauth_data;
        $record['oauth_user'] = $oauth_data['uname'];
        $record['oauth_location'] = $oauth_data['location'];
        $this->activeRecord = $record;
        return $record;
    }

    protected function authUser()
    {
        $svcobj = Openbizx::getService(AUTH_SERVICE);
        return $svcobj->authenticateUser($this->username, $this->password);
    }

}
