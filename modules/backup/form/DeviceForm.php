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
 * @version   $Id: DeviceForm.php 3351 2012-05-31 05:33:35Z rockyswen@gmail.com $
 */

use Openbizx\Easy\EasyForm;

class DeviceForm extends EasyForm
{
	public function canDeleteRecord($dataRec)
	{
		if($dataRec['system']==1){
			return false;
		}else{
			return parent::canDeleteRecord($dataRec);
		}
	}
	
	public function canDisplayForm()
    {
    	switch(strtolower($this->formType))
        {
       		case 'edit':
		        $dataRec = $this->fetchData();
		    	if($dataRec['system']==1){
					return false;
				}else{
					return parent::canDisplayForm();
				}
        		break;        		      		
        }
    	return parent::canDisplayForm();
        
    }
}
