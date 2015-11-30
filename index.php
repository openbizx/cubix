<?php

/**
 * Openbizx Cubi Application Platform
 *
 * LICENSE http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 *
 * @package
 * @copyright Copyright (c) 2005-2011, Openbiz Technology LLC
 * @license   http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 * @link      http://code.google.com/p/openbiz-cubi/
 * @version   $Id: index.php 5185 2013-01-19 15:34:13Z hellojixian@gmail.com $
 */
/** @todo need to move to header of view. */
header("Cache-Control: no-cache");
header("Pragma: no-cache");

$start = (float) array_sum(explode(' ', microtime()));

$installStatus = new InstallStatus;
if ( !$installStatus->isAppInstalled() ) {
    $installStatus->redirectToInstaller();
}

include 'bin/app_init.php';

$app = new Openbizx\Application();

// $app->setBasePath(dirname(__FILE__));
$app->run();

$end = (float) array_sum(explode(' ', microtime()));
echo "Processing time: " . sprintf("%.4f", ($end - $start)) . " seconds";

class InstallStatus {

    /**
     * Check, is the app installed?
     * @return boolean
     * @todo Need move to Installer class
     */
    
    /**
     * Check, is the app installed?
     * @return boolean
     */
    function isAppInstalled() {
        return (is_file(dirname(__FILE__) . '/files/install.lock') && filesize(dirname(__FILE__) . '/files/install.lock') == 1);
    }

    /**
     * Redirect to installer script
     * @todo Need move to Installer class.
     */
    function redirectToInstaller() {
        $script_name = $_SERVER['SCRIPT_NAME'];
        $url = str_replace("index.php", "install/", $script_name);
        echo "<script>location.href='$url'</script>";
        exit;
    }

}
