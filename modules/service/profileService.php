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
 * @version   $Id: profileService.php 5003 2012-12-31 14:09:07Z hellojixian@gmail.com $
 */


use Openbizx\Openbizx;

/**
 * profileService is class that handle user profile information.
 * this service accessed by Openbizx::getService( PROFILE_SERVICE ),
 * example :
 * <code>
 *      $profileService = Openbizx::getService( PROFILE_SERVICE );
 *      $profileName = $profileService->GetProfileName( $accountId, $type );
 * </code> 
 */
class profileService
{

    protected $name = "ProfileService";
    protected $profile;
    protected $profileObj = "contact.do.ContactSystemDO";
    protected $contactObj = "contact.do.ContactSystemDO";
    protected $userDataObj = "system.do.UserSystemDO";
    protected $groupDataObj = "system.do.GroupDO";
    protected $user_roleDataObj = "system.do.UserRoleDO";
    protected $user_groupDataObj = "system.do.UserGroupDO";

    public function __construct(&$xmlArr)
    {
        //$this->readMetadata($xmlArr);
    }

    protected function readMetadata(&$xmlArr)
    {
        //$this->profileObj = $xmlArr["PLUGINSERVICE"]["ATTRIBUTES"]["BIZDATAOBJ"];
    }

    /**
     * Initialize user profile
     * 
     * @param string $username
     * @return array
     */
    public function initProfile($username)
    {
        //clear ACL Cache
        Openbizx::getService(ACL_SERVICE)->clearACLCache();
        $this->profile = $this->initDBProfile($username);
        Openbizx::$app->getSessionContext()->setVar("_USER_PROFILE", $this->profile);
        //load preference
        $preferenceService = Openbizx::getService(OPENBIZ_PREFERENCE_SERVICE);
        $preferenceService->initPreference($this->profile["Id"]);

        return $this->profile;
    }

    public function getProfile($attribute = null)
    {
        if (!$this->profile) {
            $this->profile = Openbizx::$app->getSessionContext()->getVar("_USER_PROFILE");
        }

        if (!$this->profile) {
            $this->getProfileByCookie();
            if (!$this->profile) {
                return null;
            }
        }
        if ($attribute) {
            if (isset($this->profile[$attribute])) {
                return $this->profile[$attribute];
            } else {
                return null;
            }
        }
        return $this->profile;
    }

    /**
     * Set user profile
     * 
     * @param type $profile 
     */
    public function setProfile($profile)
    {
        $this->profile = $profile;
    }

    /**
     * Create user profile
     * 
     * @param type $userId
     * @return type 
     */
    public function createProfile($userId = null)
    {
        if (!$userId) {
            $userId = $this->getProfile("Id");
        }

        $profileDo = Openbizx::getObject($this->profileObj, 1);
        $userInfo = Openbizx::getObject($this->userDataObj, 1)->fetchById($userId);
        $profileArray = array(
            "first_name" => $userInfo['username'],
            "last_name" => $userInfo['username'],
            "display_name" => $userInfo['username'],
            "fast_index" => substr(strtolower($userInfo['username']), 0, 1),
            "email" => $userInfo['email'],
            "company" => "N/A",
            "user_id" => $userId,
            "owner_id" => $userId,
            "group_perm" => '1',
            "type_id" => '1',
            "other_perm" => '1',
        );
        $profileId = $profileDo->insertRecord($profileArray);
        return $profileId;
    }

    public function checkExist($profileId)
    {
        $profileDo = Openbizx::getObject($this->profileObj, 1);
        $profile = $profileDo->fetchById($profileId);

        if ($profile) {
            return true;
        } else {
            return false;
        }
    }

    protected function getProfileByCookie()
    {
        //print_r($_COOKIE);
        if (isset($_COOKIE["SYSTEM_SESSION_USERNAME"]) && isset($_COOKIE["SYSTEM_SESSION_PASSWORD"])) {
            $username = $_COOKIE["SYSTEM_SESSION_USERNAME"];
            $password = $_COOKIE["SYSTEM_SESSION_PASSWORD"];

            $svcobj = Openbizx::getService(AUTH_SERVICE);
            if ($svcobj->authenticateUserByCookies($username, $password)) {
                $this->InitProfile($username);
            } else {
                setcookie("SYSTEM_SESSION_USERNAME", null, time() - 100, "/");
                setcookie("SYSTEM_SESSION_PASSWORD", null, time() - 100, "/");
            }
        }
        return null;
    }

    protected function initDBProfile($username)
    {
        // fetch user record
        $userDo = Openbizx::getObject($this->userDataObj);
        if (!$userDo) {
            return false;
        }
        $userSet = $userDo->directFetch("[username]='$username'", 1);
        if (!$userSet) {
            return null;
        }

        // set the profile array
        $userId = $userSet[0]['Id'];
        
        //$profile = $userSet[0];
        $profile = array();
        $profile['password'] = null;
        $profile['enctype'] = null;

        $profileDo = Openbizx::getObject($this->profileObj, 1);
        if (!$profileDo) {
            return false;
        }

        $profileSet = $profileDo->directFetch("[user_id]='$userId'", 1);
        if ($profileSet) {
            $profileSet = $profileSet[0];
            if ($profileSet != null) {
                foreach ($profileSet as $key => $value) {
                    $profile["profile_" . $key] = $value;
                }
            }
        }

        
        // fetch roles and set profile roles
        $userRoleDo = Openbizx::getObject($this->user_roleDataObj);
        $userRoleSet = $userRoleDo->directFetch("[user_id]='$userId'");


        if ($userRoleSet) {
            foreach ($userRoleSet as $record) {
                $profile['roles'][] = $record['role_id'];
                $profile['roleNames'][] = $record['role_name'];
                $profile['roleStartpage'][] = $record['role_startpage'];
            }
        }
        
        // fetch groups and set profile groups
        $userGroupDo = Openbizx::getObject($this->user_groupDataObj);
        $userGroupSet = $userGroupDo->directFetch("[user_id]='$userId'");
        if ($userGroupSet) {
            $profile['default_group'] = null;
            foreach ($userGroupSet as $record) {
                $profile['groups'][] = $record['group_id'];
                $profile['groupNames'][] = $record['group_name'];
                if ($record['default'] == 1 && $profile['default_group'] == null) {
                    $profile['default_group'] = $record['group_id'];
                    $profile['default_group_name'] = $record['group_name'];
                }
            }
        }
        if ($profile['default_group'] == null) {
            $profile['default_group'] = $userGroupSet[0]['group_id'];
            $profile['default_group_name'] = $userGroupSet[0]['group_name'];
        }
        return $profile;
    }

    /**
     *
     * @param type $userId 
     */
    public function switchUserProfile($userId)
    {
        //get previously profile
        if (!Openbizx::$app->getSessionContext()->getVar("_PREV_USER_PROFILE")) {
            $prevProfile = Openbizx::$app->getSessionContext()->getVar("_USER_PROFILE");
            Openbizx::$app->getSessionContext()->clearVar("_USER_PROFILE");
            Openbizx::$app->getSessionContext()->setVar("_PREV_USER_PROFILE", $prevProfile);
        }
        Openbizx::$app->initUserProfile($userId);
    }

    public function getGroupName($group_id, $type = 'full')
    {
        $groupName = Openbizx::getObject($this->groupDataObj)->fetchById($group_id)->objectName;
        if ($groupName) {
            return $groupName;
        } else {
            return "-- Not Available --";
        }
    }

    public function getProfileName($account_id, $type = 'full')
    {
        $do = Openbizx::getObject($this->userDataObj);
        if (!$do)
            return "";
        if ($account_id == 0) {
            $msg = "-- Not Available --";
            return $msg;
        }

        $rs = $do->fetchById($account_id);
        if (!$rs) {
            $msg = "-- Deleted User ( UID:$account_id ) --";
            return $msg;
        }
        $contact_do = Openbizx::getObject($this->contactObj);
        $contact_rs = $contact_do->directFetch("[user_id]='$account_id'", 1);
        if (count($contact_rs) == 0) {
            //$name = $rs['username']." &lt;".$rs['email']."&gt;";
            $name = $rs['username'];
            $email = $rs['email'];
            if ($email) {
                $name.=" <$email>";
            }
        } else {
            $contact_rs = $contact_rs[0];
            if ($contact_rs['email']) {
                $email = $contact_rs['email'];
            } else {
                $email = $rs['email'];
            }
            $name = $contact_rs['display_name'];
            if ($email && $type == 'full') {
                $name.=" <$email>";
            }
        }
        return $name;
    }

    public function getProfileId($account_id)
    {
        $do = Openbizx::getObject($this->userDataObj);
        if (!$do) {
            return "";
        }
        if ($account_id == 0) {
            $profile_id = 0;
            return $profile_id;
        }
        $rs = $do->fetchById($account_id);
        if (!$rs) {
            $profile_id = 0;
            return $profile_id;
        }
        $contact_do = Openbizx::getObject($this->contactObj);
        $contact_rs = $contact_do->directFetch("[user_id]='$account_id'", 1);
        if (count($contact_rs) > 0) {
            $contact_rs = $contact_rs[0];
            $profile_id = $contact_rs['Id'];
        }
        return $profile_id;
    }

    public function getProfileEmail($account_id)
    {
        $do = Openbizx::getObject($this->userDataObj);
        if (!$do)
            return "";


        $rs = $do->fetchById($account_id);
        if (!$rs) {
            $msg = "-- Deleted User ( UID:$account_id ) --";
            return $msg;
        }

        return $rs['email'];
    }

}
