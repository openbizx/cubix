<?php
/**
 * Openbizx Cubi Application Platform
 *
 * LICENSE http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 *
 * @package   cubi.extend.widget
 * @copyright Copyright (c) 2005-2011, Openbiz Technology LLC
 * @license   http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 * @link      http://code.google.com/p/openbiz-cubi/
 * @version   $Id: ExtendSettingForm.php 3360 2012-05-31 06:00:17Z rockyswen@gmail.com $
 */

use Openbizx\Easy\EasyForm;

class ExtendSettingForm extends EasyForm
{
	public $parentFormElementMeta;
	public $accessSelectFrom;		
	
	public function loadStatefullVars($sessionContext)
    {
        $sessionContext->loadObjVar($this->objectName, "ParentFormElementMeta", $this->parentFormElementMeta);
        return parent::loadStatefullVars($sessionContext);
    }

    public function saveStatefullVars($sessionContext)
    {
        $sessionContext->saveObjVar($this->objectName, "ParentFormElementMeta", $this->parentFormElementMeta);
        return parent::saveStatefullVars($sessionContext);       
    }

    public function fetchDataSet()
    {
    	$this->accessSelectFrom = $this->parentFormElementMeta["ATTRIBUTES"]['ACCESSSELECTFROM'];
    	return parent::fetchDataSet();
    }
    
   
}
