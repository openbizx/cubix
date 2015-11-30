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
 * @version   $Id: ModuleChangeLogForm.php 3372 2012-05-31 06:19:06Z rockyswen@gmail.com $
 */
use Openbizx\Easy\EasyFormGrouping;

class ModuleChangeLogForm extends EasyFormGrouping
{

    public function loadAll()
    {
        include_once (Openbizx::$app->getModulePath() . "/system/lib/ModuleLoader.php");
        $_moduleArr = glob(Openbizx::$app->getModulePath() . "/*");
        $moduleArr[0] = "system";
        $moduleArr[1] = "menu";
        foreach ($_moduleArr as $_module) {
            $_module = basename($_module);
            $moduleArr[] = $_module;
        }

        foreach ($moduleArr as $moduleName) {
            $loader = new ModuleLoader($moduleName);
            $loader->loadChangeLog();
        }

        $this->updateForm();
    }

}
