<?php
/**
 * Openbizx Cubi Application Platform
 *
 * LICENSE http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 *
 * @package   cubi.service.fpdf.font
 * @copyright Copyright (c) 2005-2011, Openbiz Technology LLC
 * @license   http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 * @link      http://code.google.com/p/openbiz-cubi/
 * @version   $Id: courier.php 3371 2012-05-31 06:17:21Z rockyswen@gmail.com $
 */

for($i=0;$i<=255;$i++)
	$fpdf_charwidths['courier'][chr($i)]=600;
$fpdf_charwidths['courierB']=$fpdf_charwidths['courier'];
$fpdf_charwidths['courierI']=$fpdf_charwidths['courier'];
$fpdf_charwidths['courierBI']=$fpdf_charwidths['courier'];
?>
