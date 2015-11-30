<?php
/**
 * Openbizx Cubi Application Platform
 *
 * LICENSE http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 *
 * @package   cubi.contact.view
 * @copyright Copyright (c) 2005-2011, Openbiz Technology LLC
 * @license   http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 * @link      http://code.google.com/p/openbiz-cubi/
 * @version   $Id: ContactListView.php 3356 2012-05-31 05:47:51Z rockyswen@gmail.com $
 */

use Openbizx\Openbizx;
use Openbizx\Object\MetaIterator;
use Openbizx\Easy\WebPage;

class ContactListView extends WebPage
{
	protected function readMetadata(&$xmlArr)
    {
        parent::readMetaData($xmlArr);
        unset($this->formRefs);
        $formRefXML = $this->getDefaultMainForm($xmlArr["WEBPAGE"]["FORMREFERENCES"]["REFERENCE"]);
        $this->formRefs = new MetaIterator($formRefXML,"FormReference",$this);
    }
    
    public function getDefaultMainForm(&$xmlArr)
    {
        $formObj= Openbizx::getObject("contact.widget.ViewSelectorLeftWidget");
        $targetForm = $formObj->getViewMode();
        $newForm = array(
			"ATTRIBUTES"=>array(
				"NAME"=>$targetForm
				),
			"VALUE"=>null
		);
		$newArr = array($xmlArr,$newForm);
		return $newArr;
    }
}