<?php

/**
 * Openbizx Cubi Application Platform
 *
 * LICENSE http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 *
 * @package   cubi.bin
 * @copyright Copyright (c) 2005-2011, Openbiz Technology LLC
 * @license   http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 * @link      http://code.google.com/p/openbiz-cubi/
 * @version   $Id: app_init.php 3815 2012-08-02 16:07:17Z rockyswen@gmail.com $
 */
include_once ('device_util.php');

define('SITE_URL','http://localhost/cubi');

require(__DIR__ . '/../vendor/autoload.php');

// init class 
//include_once(__DIR__ . "/../vendor/openbizx/openbizx/src/init_class_loader.php");

include_once('openbiz_consts.php');
include_once(__DIR__ . "/../../cubix/vendor/openbizx/openbizx/src/sysheader_inc.php");
include_once('cubi_consts.php');

/* OPENBIZ_APP_URL is /a/b in case of http://host/a/b/index.php?... */
function get_app_url() {    
    $appHome = str_replace('\\', '/', OPENBIZ_APP_PATH);
    if (isset($_SERVER['DOCUMENT_ROOT'])) {
        $appPath = str_replace($_SERVER['DOCUMENT_ROOT'], '', $appHome);
    } else {
        $appPath = $appHome;
    }
    if ($appPath == $appHome) {        
        //$doc_root = str_replace('\\','/',dirname(OPENBIZ_APP_PATH));  //support for apache alias path
        $doc_root = str_replace('\\', '/', OPENBIZ_APP_PATH);
        $appPath = str_replace($doc_root, "", $appHome);
    }
    if (substr($appPath, 0, 1) != '/' && strlen($appPath) > 0) {
        $appPath = '/' . $appPath;
    }
    if ($appPath == '/') {
        $app_url = '';
    } else {
        if (!isset($_SERVER['HTTP_HOST'])) {
            $app_url = '';
        } else {
            $app_url = $appPath;
        }
    }
    return $app_url;
}
