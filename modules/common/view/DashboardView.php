<?php
/**
 * Openbizx Cubi Application Platform
 *
 * LICENSE http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 *
 * @package   cubi.myaccount.view
 * @copyright Copyright (c) 2005-2011, Openbiz Technology LLC
 * @license   http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 * @link      http://code.google.com/p/openbiz-cubi/
 * @version   $Id: DashboardView.php 3365 2012-05-31 06:07:55Z rockyswen@gmail.com $
 */

/**
 * Openbizx Cubi Application Platform
 *
 * LICENSE http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 *
 * @package   cubi.common.lib
 * @copyright Copyright (c) 2005-2011, Openbiz Technology LLC
 * @license   http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 * @link      http://code.google.com/p/openbiz-cubi/
 * @version   $Id$
 */


use Openbizx\Openbizx;
use Openbizx\Object\MetaIterator;
use Openbizx\Easy\WebPage;

class DashboardView extends WebPage
{
	protected $userWidgetDO = "common.do.UserWidgetDO";	
	protected $columns;
	
	protected function readMetadata(&$xmlArr)
    {
        parent::readMetaData($xmlArr);
		$formRefXML = $this->getFormReferences();		
        $this->formRefs = new MetaIterator($formRefXML,"FormReference",$this);
	}
	
	protected function getFormReferences()
	{
		// get user widgets of this view
		$user_id = Openbizx::$app->getUserProfile("Id");
		$viewName = $this->objectName;
		$searchRule="[user_id]='$user_id' AND [view]='$viewName'";
		$do = Openbizx::getObject($this->userWidgetDO);
		$formRecs = $do->directfetch($searchRule);
		// if no user widgets found, get system widgets of this view
		if (count($formRecs)==0) {
			$searchRule="[user_id]=0 AND [view]='$viewName'";
			$formRecs = $do->directfetch($searchRule);
		}
		$formRefXML = array();
		foreach($formRecs as $form){
			$formRefXML[] = array(
				"ATTRIBUTES"=>array(
					"NAME"=>$form['widget']
					),
				"VALUE"=>null
			);
			$this->columns[$form['column']][] = $form['widget'];
		}

		return $formRefXML;
	}
	
	public function outputAttrs() 
	{
		$out = parent::outputAttrs();
		$out['columns'] = $this->columns;
		//print_r($out['columns']);
		return $out;
	}
	
	public function loadWidgetPicker()
	{
		$widgetPickerForm = "myaccount.form.WidgetPickForm";
		$formObj = Openbizx::getObject($widgetPickerForm);
		Openbizx::$app->getClientProxy()->redrawForm("DIALOG", $formObj->render());
	}
}
