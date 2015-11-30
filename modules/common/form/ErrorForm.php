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
 * @version   $Id: ErrorForm.php 5017 2013-01-01 11:28:44Z hellojixian@gmail.com $
 */
use Openbizx\Openbizx;
use Openbizx\Easy\EasyForm;

class ErrorForm extends EasyForm
{

    protected $isShowError = false;

    function __construct(&$xmlArr)
    {
        parent::readMetadata($xmlArr);
    }

    public function loadStatefullVars($sessionContext)
    {
        parent::loadStatefullVars($sessionContext);
        $sessionContext->loadObjVar($this->objectName, "Errors", $this->errors);
        $sessionContext->loadObjVar($this->objectName, "showError", $this->isShowError);
    }

    public function saveStatefullVars($sessionContext)
    {
        parent::saveStatefullVars($sessionContext);
        $sessionContext->saveObjVar($this->objectName, "Errors", $this->errors);
        $sessionContext->saveObjVar($this->objectName, "showError", $this->isShowError);
    }

    public function getWebpageObject()
    {
        $viewObj = Openbizx::getObject("common.view.ErrorView");
        return $viewObj;
    }

    public function fetchData()
    {
        if ($_GET['ob_err_msg']) {
            $this->errors = array("system" => $_GET['ob_err_msg']);
            // $this->errors['system']="asas a ";
        }
        return parent::fetchData();
    }

    public function showError()
    {
        if ($this->isShowError) {
            $this->isShowError = false;
        } else {
            $this->isShowError = true;
        }
        $this->rerender();
    }

    public function outputAttrs()
    {
        $result = parent::outputAttrs();
        $result['show_error'] = $this->isShowError;
        return $result;
    }

    public function Report()
    {
        //send an email to admin includes error messages;
        $system_uuid = Openbizx::getService("system.lib.CubiService")->getSystemUUID();

        $report = array(
            "system_uuid" => $system_uuid,
            "error_info" => $this->errors["system"],
            "server_info" => $_SERVER,
            "php_version" => phpversion(),
            "php_extension" => get_loaded_extensions()
        );

        $reportId = Openbizx::getObject("common.lib.ErrorReportService")->report($report);
        $this->notices = array("status" => "REPORTED",
            "report_id" => $reportId);
        $this->ReRender();
    }

}
