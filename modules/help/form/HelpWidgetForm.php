<?php

/**
 * Openbizx Cubi Application Platform
 *
 * LICENSE http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 *
 * @package   cubi.help.form
 * @copyright Copyright (c) 2005-2011, Openbiz Technology LLC
 * @license   http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 * @link      http://code.google.com/p/openbiz-cubi/
 * @version   $Id: HelpWidgetForm.php 4608 2012-11-05 10:08:31Z hellojixian@gmail.com $
 */
use Openbizx\Openbizx;
use Openbizx\Easy\EasyForm;

class HelpWidgetForm extends EasyForm
{

    public $encodedURL;
    public $tutorialId = false;
    protected $categoryDO = "help.do.HelpCategoryDO";
    protected $categoryMappingDO = "help.do.HelpCategoryMappingDO";

    protected function getUrl()
    {
        if ($_SERVER["REDIRECT_QUERY_STRING"]) {
            $url = $_SERVER["REDIRECT_QUERY_STRING"];
        } elseif (preg_match("/\?\/?(.*?)(\.html)?$/si", $_SERVER['REQUEST_URI'], $match)) {
            //supports for http://localhost/?/user/login format
            //supports for http://localhost/index.php?/user/login format
            $url = $match[1];
        } elseif (strlen($_SERVER['REQUEST_URI']) > strlen($_SERVER['SCRIPT_NAME'])) {
            //supports for http://localhost/index.php/user/login format
            $url = str_replace($_SERVER['SCRIPT_NAME'], "", $_SERVER['REQUEST_URI']);
            preg_match("/\/?(.*?)(\.html)?$/si", $url, $match);
            $url = $match[1];
        } else {
            // REQUEST_URI = /cubi/
            // SCRIPT_NAME = /cubi/index.php
            $url = "";
        }
        if (preg_match("/^F=RPCInvoke/si", $url)) {
            $url = "";
        }
        return $url;
    }

    public function setSearchRule()
    {
        $url = $this->GetURL();
        if (!$url) {
            return;
        }

        $this->encodedURL = base64_encode('/' . $url);
        $this->tutorialId = Openbizx::getService("help.lib.TutorialService")->getTutorialId('/' . $url);

        //search cat_id from mapping table
        $mappingObj = Openbizx::getObject($this->categoryMappingDO, 1);

        //@todo: $url need to be filtered before use in database
        $records = $mappingObj->directFetch("[url]='$url'");
        if (count($records) == 1) {
            $cat_id = (int) $records[0]['cat_id'];
        } else {
            //if no matched, generate record from category table url_match
            $categoryObj = Openbizx::getObject($this->categoryDO, 1);
            $records = $categoryObj->directFetch();
            foreach ($records as $record) {
                $match = $record['url_match'];
                if ($match) {

                    $pattern = "/" . str_replace('/', '\\/', $match) . "/si";
                    $pattern = "@" . $match . "@si";
                    if (preg_match($pattern, "/" . $url)) {
                        $cat_id = $record['Id'];
                        //cache it into database;
                        $obj_array = array(
                            "cat_id" => $cat_id,
                            "url" => $url,
                        );
                        $mappingObj->insertRecord($obj_array);
                        break;
                    }
                }
            }
        }

        $this->searchRule = "[category_id]='$cat_id'";
    }

    public function fetchDataSet()
    {
        $this->SetSearchRule();
        return parent::fetchDataSet();
    }

    public function render()
    {
        $result = parent::render();
        if ($result) {
            $script = "<script>setTimeout(\"Openbizx.CallFunction('" . $this->objectName . ".AutoShowTutorial($this->encodedURL)')\",1000);</script>";
            $result.= $script;
        }
        return $result;
    }

    public function showTutorial($tutoralId)
    {
        if (!$tutoralId) {
            return;
        }
        Openbizx::getService("help.lib.TutorialService")->ShowTutorial($tutoralId, $this);
    }

    public function AutoShowTutorial($url_base64 = null)
    {
        if (!$url_base64) {
            return;
        }
        $url = base64_decode($url_base64);
        Openbizx::getService("help.lib.TutorialService")->autoShowTutorial($url, $this);
    }

}
