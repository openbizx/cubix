<?php

use Openbizx\Openbizx;
use Openbizx\Object\MetaObject;

class SystemService extends MetaObject
{

    public function getDefaultGroupID()
    {
        $groupRec = Openbizx::getObject("system.do.GroupDO")->fetchOne("[default]='1'", "[Id] DESC");
        if ($groupRec) {
            $Id = $groupRec['Id'];
        }
        return (int) $Id;
    }

    public function getDefaultRoleID()
    {
        $roleRec = Openbizx::getObject("system.do.RoleDO")->fetchOne("[default]='1'", "[Id] DESC");
        if ($roleRec) {
            $Id = $roleRec['Id'];
        }
        return (int) $Id;
    }
}
