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
 * @version   $Id: HelpReadForm.php 3345 2012-05-31 05:04:56Z rockyswen@gmail.com $
 */

use Openbizx\Easy\EasyForm;

class HelpReadForm extends EasyForm
{
	public function fetchData(){
		$data = parent::fetchData();
		$data['content'] = str_replace("/cubi/", OPENBIZ_APP_URL.'/', $data['content']);
		return $data;
	}
}
