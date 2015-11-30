<?php
/**
 * Openbizx Cubi Application Platform
 *
 * LICENSE http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 *
 * @package   \
 * @copyright Copyright (c) 2005-2011, Openbiz Technology LLC
 * @license   http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 * @link      http://code.google.com/p/openbiz-cubi/
 * @version   $Id: rs.php 4212 2012-09-16 06:49:38Z rockyswen@gmail.com $
 */
 
// url format. http://host/css.php?f=css/a.css,themes/default/css/b.css&min=1
// e.g. http://host/css.php?f=themes/default/css/system_backend.css
// 
// include app.inc
//include_once "bin/app_init.php";
define('OPENBIZ_APP_PATH',dirname(__FILE__));
define('OPENBIZ_RESOURCE_PATH', OPENBIZ_APP_PATH.DIRECTORY_SEPARATOR."resources");
define('CUBI_CACHE_DATA_PATH', OPENBIZ_APP_PATH.DIRECTORY_SEPARATOR."files".DIRECTORY_SEPARATOR."cache".DIRECTORY_SEPARATOR."data");

$file = $_GET['f'];
// get extention of given file
$list = explode('.',$file);
$ext = $list[count($list)-1];
if ($ext == 'jpg' || $ext == 'gif' || $ext == 'png' || $ext == 'swf') {
	$_GET['img'] = $file;
    include "bin/img.php";
}
/*else {
    // include minify index
    include "bin/min/index.php";
}
*/
?>