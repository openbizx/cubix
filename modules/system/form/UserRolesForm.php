<?php

/**
 * Openbizx Cubi Application Platform
 *
 * LICENSE http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 *
 * @package   cubi.system.form
 * @copyright Copyright (c) 2005-2011, Openbiz Technology LLC
 * @license   http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 * @link      http://code.google.com/p/openbiz-cubi/
 * @version   $Id: UserRolesForm.php 3372 2012-05-31 06:19:06Z rockyswen@gmail.com $
 */
use Openbizx\Openbizx;
use Openbizx\Easy\EasyForm;

class UserRolesForm extends EasyForm
{

    public function SetDefault($role_id = null)
    {
        if ($role_id == null) {
            $role_id = (int) Openbizx::$app->getClientProxy()->getFormInputs('_selectedId');
        }
        $user_id = (int) Openbizx::getObject('system.form.UserDetailForm')->recordId;

        $roleDo = Openbizx::getObject("system.do.UserRoleDO", 1);
        $roleDo->updateRecords("[default]=0", "[user_id]='$user_id'");
        $roleDo->updateRecords("[default]=1", "[user_id]='$user_id' and [role_id]='$role_id'");

        $this->recordId = $role_id;
        $this->UpdateForm();
    }

}
