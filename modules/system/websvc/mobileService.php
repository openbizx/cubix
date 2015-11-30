<?php

use Openbizx\Openbizx;

require_once Openbizx::$app->getModulePath() . '/websvc/lib/WebsvcService.php';

class mobileService extends WebsvcService
{

    public function getServerInfo()
    {
        $result = array(
            'system_name' => OPENBIZ_DEFAULT_SYSTEM_NAME,
            'system_icon' => SITE_URL . '/images/cubi_logo_large.png'
        );
        return $result;
    }

    public function login()
    {
        $username = $_GET['username'];
        $password = $_GET['password'];
        $svcobj = Openbizx::getService(AUTH_SERVICE);

        if ($svcobj->authenticateUser($username, $password)) {
            // after authenticate user: 1. init profile
            $profile = Openbizx::$app->InitUserProfile($username);

            // after authenticate user: 2. insert login event
            $eventlog = Openbizx::getService(OPENBIZ_EVENTLOG_SERVICE);
            $logComment = array($username, $_SERVER['REMOTE_ADDR']);
            $eventlog->log("LOGIN", "MSG_LOGIN_SUCCESSFUL", $logComment);

            // after authenticate user: 3. update login time in user record
            $userObj = Openbizx::getObject('system.do.UserDO');

            $userRec = $userObj->fetchOne("[username]='$username'");
            $userRec['lastlogin'] = date("Y-m-d H:i:s");
            $userId = $userRec['Id'];
            $userRec->save();
        }
        $result = array(
            "user_id" => $userId
        );
        return $result;
    }

}
