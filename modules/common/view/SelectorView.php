<?php
/**
 * Openbizx Cubi Application Platform
 *
 * LICENSE http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 *
 * @package   cubi.common.view
 * @copyright Copyright (c) 2005-2011, Openbiz Technology LLC
 * @license   http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 * @link      http://code.google.com/p/openbiz-cubi/
 * @version   $Id: SelectorView.php 4734 2012-11-14 14:18:37Z hellojixian@gmail.com $
 */

use Openbizx\Openbizx;
use Openbizx\Object\MetaIterator;
use Openbizx\Easy\WebPage;

class SelectorView extends WebPage
{
	
    public $formSelector;

	protected function readMetadata(&$xmlArr)
    {
        parent::readMetaData($xmlArr);
        $this->formRefs = null;
        $this->formSelector = isset($xmlArr["WEBPAGE"]["ATTRIBUTES"]["FROMSELECOTR"]) ? $xmlArr["WEBPAGE"]["ATTRIBUTES"]["FROMSELECOTR"] : null;
        $formRefXML = $this->getDefaultMainForm($xmlArr["WEBPAGE"]["FORMREFERENCES"]["REFERENCE"]);
        $this->formRefs = new MetaIterator($formRefXML,"FormReference",$this);
    }
    
    public function getDefaultMainForm(&$xmlArr)
    {
    	$newForm = array(
			"ATTRIBUTES"=>array(
				"NAME"=>$this->formSelector
				),
			"VALUE"=>null
		);
		$xmlArr = $newForm;
        $formObj= Openbizx::getObject($this->formSelector);
    	if(!$formObj){
			return;
		}
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