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
 * @version   $Id: _forward.php 5173 2013-01-19 06:51:01Z hellojixian@gmail.com $
 */

use Openbizx\Web\Request;

// map url parameters to openbiz view, form, ...
//http://localhost/?/user/login			 => http://localhost/bin/controller.php?view=user.view.LoginView
//http://localhost/?/user/reset_password => http://localhost/bin/controller.php?view=user.view.RestPasswordView
//http://localhost/?/article/1 			 => http://localhost/bin/controller.php?view=page.view.ArticleView&fld:Id=1
//($DEFAULT_MODULE="page")
//http://localhost/?/article/1/f_catid_20=> http://localhost/bin/controller.php?view=page.view.ArticleView&fld:Id=1&fld:catid=20
//($DEFAULT_MODULE="page")
//http://localhost/?/article/catid_20 	 => http://localhost/bin/controller.php?view=page.view.ArticleView&catid=20
//($DEFAULT_MODULE="page")
define("OPENBIZ_USE_CUSTOM_SESSION_HANDLER", true);

include 'app_init.php';


    //$profile = Openbizx::$app->getUserProfile();
    $profile = Openbizx::$app->getSessionContext()->getVar("_USER_PROFILE");
    echo __FILE__.__LINE__;
    echo '<pre>';
    echo var_dump($profile);
    exit;

$req = new Request();

echo '<pre>';

$url = $req->getPathUri();
echo 'getPathUri  : ' . $url . '<br />';


echo var_dump($requestInfo);

$module_name = $requestInfo['module'];
$view_name   = $requestInfo['view'];
$PARAM_MAPPING = $requestInfo['uriParams'];


$TARGET_VIEW = $module_name . ".view." . $view_name;
$_GET['view'] = $_REQUEST['view'] = $TARGET_VIEW;

$req->convertUriParamsToGetVars($requestInfo['uriParams']);

echo 'getPathUri     : ' . $req->getPathUri() . '<br />';
echo 'REQUEST_URI    : ' . $_SERVER['REQUEST_URI'] . '<br />';
echo '$TARGET_VIEW   : ' . $TARGET_VIEW . '<br />';
echo '$PARAM_MAPPING : <br />';
echo var_dump($PARAM_MAPPING);
echo '</pre>';
exit;



$foo = __FILE__;
include dirname(__FILE__) . '/controller.php';






