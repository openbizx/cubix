<?php
/**
 * Openbizx Cubi Application Platform
 *
 * LICENSE http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 *
 * @package   cubi.market.application.form
 * @copyright Copyright (c) 2005-2011, Openbiz Technology LLC
 * @license   http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 * @link      http://code.google.com/p/openbiz-cubi/
 * @version   $Id: AppUpdateListForm.php 3363 2012-05-31 06:04:56Z rockyswen@gmail.com $
 */

require_once 'InstalledAppListForm.php';
class AppUpdateListForm extends InstalledAppListForm
{
	public function fetchDataSet()
	{
		$resultSet = parent::fetchDataSet();
		foreach($resultSet as $key=>$app)
		{			
			$current_version = $app['version'];
			$latest_version = $app['latest_version'];			
			if(version_compare($current_version, $latest_version) != -1)
			{
				unset($resultSet[$key]);
			}else{
				$app['description'] = $app['version_description'];
				$newResultSet[] = $app;
			}
		}		
		return $newResultSet;
	}		
}
