<?php
/**
 * Openbizx Cubi Application Platform
 *
 * LICENSE http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 *
 * @package   cubi.common.form
 * @copyright Copyright (c) 2005-2011, Openbiz Technology LLC
 * @license   http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 * @link      http://code.google.com/p/openbiz-cubi/
 * @version   $Id: WidgetPickForm.php 3365 2012-05-31 06:07:55Z rockyswen@gmail.com $
 */


use Openbizx\Openbizx;
use Openbizx\Easy\PickerForm;

class WidgetPickForm extends PickerForm
{
	protected $userWidgetDOName = "common.do.UserWidgetDO";
	
	public function PicktoParent()
	{
	 	if ($id==null || $id=='')
            $id = Openbizx::$app->getClientProxy()->getFormInputs('_selectedId');

        $selIds = Openbizx::$app->getClientProxy()->getFormInputs('row_selections', false);
        if ($selIds == null)
            $selIds[] = $id;
        
        foreach ($selIds as $id)
        {
            $rec = $this->getDataObj()->fetchById($id);
			$widgetName = $rec['name'];
			$this->addWidget($widgetName);
        }

		$this->close();
        // reload current page
		Openbizx::$app->getClientProxy()->runClientFunction("window.location.reload()");
	}
	
	// add a widget to current dashboard view
	protected function addWidget($widgetName)
	{
		// add widget to user_widget table
		$userWidgetDo = Openbizx::getObject($this->userWidgetDOName);
		$userWidgetTable = $userWidgetDo->mainTableName;
		$db = $userWidgetDo->getDbConnection();
		
		$myProfile = Openbizx::$app->getUserProfile();
		$myUserId = $myProfile['Id'];
		$currentView = Openbizx::$app->getCurrentViewName();
		
		$searchRule = "[user_id]=$myUserId and [widget]='$widgetName' and [view]='$currentView'";
		$record = $userWidgetDo->fetchOne($searchRule);
		if ($record) {
			Openbizx::$app->getClientProxy()->showClientAlert("The widget $widgetName is already on the page.");
		}
		else {
			$data = array('user_id'=>$myUserId, 'widget'=>$widgetName, 'view'=>$currentView, 'ordering'=>0);
			$db->insert($userWidgetTable, $data);
		}
	}
}
