<?php

/**
 * Openbizx Cubi Application Platform
 *
 * LICENSE http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 *
 * @package   cubi.common.form
 * @copyright Copyright (c) 2005-2011, Openbiz Technology LLC
 * @license   http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 * @link      http://code.google.com/p/openbiz-cubi/
 * @version   $Id: LicenseForm.php 4992 2012-12-31 06:43:41Z hellojixian@gmail.com $
 */

use Openbizx\Openbizx;
use Openbizx\Easy\EasyForm;

class LicenseForm extends EasyForm
{

    public $errorCode;
    public $errorParams;
    public $sourceURL;
    public $moduleName;
    public $moduleRegister;
    public $moduleTrial;

    public function outputAttrs()
    {
        $result = parent::outputAttrs();
        $result['appInfo'] = $this->getAppInfo();
        return $result;
    }

    public function getWebpageObject()
    {
        $viewObj = Openbizx::getObject("common.view.LicenseInvalidView");
        return $viewObj;
    }

    public function saveStatefullVars($sessionContext)
    {
        if ($this->errorParams) {
            $sessionContext->saveObjVar("common.form.LicenseForm", "SourceURL", $this->sourceURL);
            $sessionContext->saveObjVar("common.form.LicenseForm", "ErrorCode", $this->errorCode);
            $sessionContext->saveObjVar("common.form.LicenseForm", "ErrorParams", $this->errorParams);
        }
        parent::saveStatefullVars($sessionContext);
    }

    public function loadStatefullVars($sessionContext)
    {
        $sessionContext->loadObjVar("common.form.LicenseForm", "SourceURL", $this->sourceURL);
        $sessionContext->loadObjVar("common.form.LicenseForm", "ErrorCode", $this->errorCode);
        $sessionContext->loadObjVar("common.form.LicenseForm", "ErrorParams", $this->errorParams);
        parent::loadStatefullVars($sessionContext);
    }

    public function getAppInfo()
    {
        if ($this->getAppRegister()) {
            $func = $this->moduleName . '_get_product_info';
            $result = $func();
        }
        return $result;
    }

    public function getAppRegister()
    {
        if (!$this->moduleName) {
            $this->getAppModuleName();
        }
        if ($this->moduleName) {
            $filename = Openbizx::$app->getModulePath() . DIRECTORY_SEPARATOR . $this->moduleName . DIRECTORY_SEPARATOR . 'register_handler.php';
            if (file_exists($filename)) {
                require_once($filename);
                $mod_register_func = strtolower($this->moduleName) . '_register_handler';
                if (function_exists($mod_register_func)) {
                    $this->moduleRegister = $mod_register_func;
                }
                $mod_trial_func = strtolower($this->moduleName) . '_trial_handler';
                if (function_exists($mod_trial_func)) {
                    $this->moduleTrial = $mod_trial_func;
                }
                return $mod_register_func;
            }
        }
        return;
    }

    public function getAppModuleName()
    {
        $current_file = $this->errorParams['current_file'];
        $current_file = str_replace(Openbizx::$app->getModulePath(), "", $current_file);
        preg_match("|[\\\/]?(.*?)[\\\/]{1}|si", $current_file, $matches);
        $this->moduleName = $matches[1];
        return $this->moduleName;
    }

    public function GoRegister()
    {
        $this->getAppRegister();
        if ($this->moduleRegister && function_exists($this->moduleRegister)) {
            $param = null;
            if (function_exists("ioncube_server_data")) {
                $param = ioncube_server_data();
            }
            return call_user_func($this->moduleRegister, $param);
        }
    }

    public function getErrorMessage()
    {
        switch ((int) $this->errorCode) {
            case 1:
                $msg = "ION_CORRUPT_FILE";
                break;
            case 2:
                $msg = "ION_EXPIRED_FILE";
                break;
            case 3:
                $msg = "ION_NO_PERMISSIONS";
                break;
            case 4:
                $msg = "ION_CLOCK_SKEW";
                break;
            case 5:
                $msg = "ION_UNTRUSTED_EXTENSION";
                break;
            case 6:
                $msg = "ION_LICENSE_NOT_FOUND";
                break;
            case 7:
                $msg = "ION_LICENSE_CORRUPT";
                break;
            case 8:
                $msg = "ION_LICENSE_EXPIRED";
                break;
            case 9:
                $msg = "ION_LICENSE_PROPERTY_INVALID";
                break;
            case 10:
                $msg = "ION_LICENSE_HEADER_INVALID";
                break;
            case 11:
                $msg = "ION_LICENSE_SERVER_INVALID";
                break;
            case 12:
                $msg = "ION_UNAUTH_INCLUDING_FILE";
                break;
            case 13:
                $msg = "ION_UNAUTH_INCLUDED_FILE";
                break;
            case 14:
                $msg = "ION_UNAUTH_APPEND_PREPEND_FILE";
                break;
        }
        $msg = $this->getMessage($msg);
        return $msg;
    }

}

