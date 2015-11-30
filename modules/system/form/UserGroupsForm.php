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
 * @version   $Id: UserGroupsForm.php 3372 2012-05-31 06:19:06Z rockyswen@gmail.com $
 */
use Openbizx\Openbizx;
use Openbizx\Easy\EasyForm;

class UserGroupsForm extends EasyForm
{

    public function SetDefault($group_id = null)
    {
        if ($group_id == null) {
            $group_id = (int) Openbizx::$app->getClientProxy()->getFormInputs('_selectedId');
        }
        $user_id = (int) Openbizx::getObject('system.form.UserDetailForm')->recordId;

        $groupDo = Openbizx::getObject("system.do.UserGroupDO", 1);
        $groupDo->updateRecords("[default]=0", "[user_id]='$user_id'");
        $groupDo->updateRecords("[default]=1", "[user_id]='$user_id' and [group_id]='$group_id'");

        $this->recordId = $group_id;
        $this->UpdateForm();
    }

}
