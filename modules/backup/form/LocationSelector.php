<?php
/**
 * Openbizx Cubi Application Platform
 *
 * LICENSE http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 *
 * @package   cubi.backup.form
 * @copyright Copyright (c) 2005-2011, Openbiz Technology LLC
 * @license   http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 * @link      http://code.google.com/p/openbiz-cubi/
 * @version   $Id: LocationSelector.php 3351 2012-05-31 05:33:35Z rockyswen@gmail.com $
 */

use Openbizx\Easy\Element\DropDownList;

class  LocationSelector extends DropDownList
{
	protected function getList()
    {    	
    	$list = parent::getList();
    	
     	foreach($list as $key=>$value)
    	{
    		switch($list[$key]['pic'])
    		{
    			default:
    			case "0":
    				$list[$key]['pic'] = OPENBIZ_RESOURCE_URL.'/backup/images/icon_type_user.png';
    				break;
    			case "1":
    				$list[$key]['pic'] = OPENBIZ_RESOURCE_URL.'/backup/images/icon_type_system.png';
    				break;    				
    		}
    	}
    	
    	return $list;
    }
}
