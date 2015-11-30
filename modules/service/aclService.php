<?PHP
/**
 * Openbizx
 *
 * @author     Rocky Swen <rocky@phpopenbiz.org>
 * @version    2.3 2009-06-01
 */

define ('OPENBIZ_DENY', 0);
define ('OPENBIZ_ALLOW', 1);
define ('OPENBIZ_ALLOW_OWNER', 2);

use Openbizx\Openbizx;

class aclService
{
    static protected $role_actionDataObj = "system.do.AclRoleActionDO";
	static protected $_accessMatrix;
	static protected $_defaultAccess = OPENBIZ_DENY;

    // TODO: conver it to AclService
    // TODO: save the data $userAccesses in session

    // return OPENBIZ_ALLOW, OPENBIZ_DENY, OPENBIZ_ALLOW_OWNER
    public static function allowAccess($res_action)
    {
    	if (!aclService::$_accessMatrix)
        {
            // get the access matrix from session
            aclService::$_accessMatrix = Openbizx::$app->getSessionContext()->getVar("_ACCESS_MATRIX");
            if (!aclService::$_accessMatrix || count(aclService::$_accessMatrix) == 0)
            {
                // get user profile
                $profile = Openbizx::$app->getUserProfile();
                
                if (!$profile) {
                    return false;
                } // user not login

                // get the user role id
                $roleIds = $profile['roles'];
                if (!$roleIds)
                    $roleIds[0] = 0; // guest
                $roleId_query = implode (",", $roleIds);

                // generate the access matrix
                
                /* @var $do BizDataObj */
                $do = Openbizx::getObject(aclService::$role_actionDataObj);
                $rs = $do->directFetch("[role_id] in ($roleId_query)");

                if (count($rs)==0)
                    return false;

                aclService::$_accessMatrix = aclService::_generateAccessMatrix($rs);
                Openbizx::$app->getSessionContext()->setVar("_ACCESS_MATRIX", aclService::$_accessMatrix);
            }

            $accessLevel = self::$_defaultAccess;	// default is deny
        }

        if (isset(aclService::$_accessMatrix[$res_action]))
            $accessLevel = aclService::$_accessMatrix[$res_action];

        switch ($accessLevel)
        {
            case OPENBIZ_DENY:  // if access level is OPENBIZ_DENY, return false
                return false;
            case OPENBIZ_ALLOW: // if access level is OPENBIZ_ALLOW or empty, return true
                return true;
            case OPENBIZ_ALLOW_OWNER:
                // if access level is OPENBIZ_ALLOW_OWNER, check the OwnerField and OwnerValue.
                // if ownerField's value == ownerValue, return true.
                return true;
        }
    }

    protected static function _generateAccessMatrix($rs)
    {
    	$accessMatrix = array();
        foreach ($rs as $row)
        {
            $resourceAction = $row['resource'].'.'.$row['action'];
            if (!isset($accessMatrix[$resourceAction]))
                $accessMatrix[$resourceAction] = $row['access_level'];
            elseif (isset($accessMatrix[$resourceAction]) && $accessMatrix[$resourceAction] < $row['access_level'])
                $accessMatrix[$resourceAction] = $row['access_level'];
        }
        return $accessMatrix;
    }

    /**
     * Clean ACL cache from session 
     */
    public function clearACLCache() 
    {
		aclService::$_accessMatrix = null;
    	Openbizx::$app->getSessionContext()->setVar("_ACCESS_MATRIX", array());
    	Openbizx::$app->getSessionContext()->clearVar("_ACCESS_MATRIX");
    }
}