<?PHP
/**
 * Openbizx Cubi Application Platform
 *
 * LICENSE http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 *
 * @package   \
 * @copyright Copyright (c) 2005-2011, Openbiz Technology LLC
 * @license   http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 * @link      http://code.google.com/p/openbiz-cubi/
 * @version   $Id: ctl.php 5273 2013-01-25 10:44:17Z hellojixian@gmail.com $
 */

//$start = (float) array_sum(explode(' ',microtime())); 
define("OPENBIZ_USE_CUSTOM_SESSION_HANDLER",true);     
include_once("bin/app_init.php");

include_once(OPENBIZ_BIN."BizApplication.php");
/*
$end = (float) array_sum(explode(' ',microtime()));
echo "Processing time: ". sprintf("%.4f", ($end-$start))." seconds"; 
*/

