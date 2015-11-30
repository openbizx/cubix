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
 * @version   $Id: LicenseActiveForm.php 3755 2012-07-29 15:55:04Z hellojixian@gmail.com $
 */

use Openbizx\Openbizx;

require_once "LicenseForm.php";

class LicenseActiveForm extends LicenseForm
{

    public $activeModuleName;
    public $lastView;

    public function readMetadata($xmlArr)
    {
        parent::readMetadata($xmlArr);
        $this->activeModuleName = Openbizx::$app->getSessionContext()->getVar("LIC_MODULE");
    }

    public function fetchData()
    {
        $result['license_code'] = $url . $this->getExistingLicenseCode();
        $this->getAppRegister();
        return $result;
    }

    protected function getRedirectPage()
    {
        $view = Openbizx::$app->getSessionContext()->getVar("LIC_SOURCE_URL");
        return array($view, "");
    }

    public function activeLicense()
    {
        $rec = $this->readInputRecord();
        $lic_code = $rec['license_code'];
        $this->setLicenseCode($lic_code);
        $this->processPostAction();
        return;
    }

    public function getExistingLicenseCode()
    {
        $lic_file = Openbizx::$app->getModulePath() . DIRECTORY_SEPARATOR . $this->activeModuleName . DIRECTORY_SEPARATOR . 'license.key';
        if (file_exists($lic_file)) {
            return file_get_contents($lic_file);
        }
    }

    public function setLicenseCode($code)
    {
        $lic_file = Openbizx::$app->getModulePath() . DIRECTORY_SEPARATOR . $this->activeModuleName . DIRECTORY_SEPARATOR . 'license.key';
        return file_put_contents($lic_file, $code);
    }

}

