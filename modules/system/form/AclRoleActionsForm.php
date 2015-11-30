<?php
/**
 * Openbizx Cubi Application Platform
 *
 * LICENSE http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 *
 * @package   cubi.system.form
 * @copyright Copyright (c) 2005-2011, Openbiz Technology LLC
 * @license   http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 * @link      http://code.google.com/p/openbiz-cubi/
 * @version   $Id: AclRoleActionsForm.php 3372 2012-05-31 06:19:06Z rockyswen@gmail.com $
 */

use Openbizx\Openbizx;
use Openbizx\Data\Helpers\QueryStringParam;
use Openbizx\Easy\EasyForm;

class AclRoleActionsForm extends EasyForm
{
	protected $_roleId;
	
	public function loadStatefullVars($sessionContext)
    {
        parent::loadStatefullVars($sessionContext);
        $sessionContext->loadObjVar($this->objectName, "_roleId", $this->_roleId);
    }

    public function saveStatefullVars($sessionContext)
    {
        parent::saveStatefullVars($sessionContext);
        $sessionContext->saveObjVar($this->objectName, "_roleId", $this->_roleId);
    }
    
	public function sortRecord($sortCol, $order='asc')
    {
        $element = $this->getElement($sortCol);
        // turn off the OnSort flag of the old onsort field
        $element->setSortFlag(null);
        // turn on the OnSort flag of the new onsort field
        if ($order == "ASC") {
            $order = "DESC";
        } else {
            $order = "ASC";
        }
        $element->setSortFlag($order);

        // change the sort rule and issue the query
        $do = Openbizx::getObject("system.do.AclActionDO");
        $do->setSortRule("[" . $element->fieldName . "] " . $order);

        // move to 1st page
        $this->currentPage = 1;

        $this->rerender();
    }
	
    
	public function fetchDataSet()
    {
        $roleId = $this->getRoleId();
        
        if($this->searchRuleBindValues){
        	QueryStringParam::setBindValues($this->searchRuleBindValues);
        }
        // fetch acl_action records
        $aclActionDO = Openbizx::getObject("system.do.AclActionDO",1);
        //var_dump($this->searchRuleBindValues);
        
        $aclActionDO->setQueryParameters($this->queryParams);

        $aclActionDO->setLimit($this->range, ($this->currentPage-1)*$this->range);
        $rs = $aclActionDO->fetch()->toArray();

        //echo '<pre>';
        //DebugLine::show(__METHOD__.__LINE__);
        //DebugLine::show(var_dump($rs));
        //return;

        $this->totalRecords = $aclActionDO->count();

        if ($this->range && $this->range > 0) {
            $this->totalPages = ceil($this->totalRecords / $this->range);
        }

        // fetch role and access
        //$this->getDataObj()->searchRule .= "[role_id]=$roleId ";        
        $this->getDataObj()->setSearchRule("[role_id]=$roleId");
        if($this->searchRule){
        	$this->getDataObj()->setSearchRule($this->searchRule);
        }
        //DebugLine::show(' -- ' . $this->searchRule);
        //return;
        $rs1 = $this->getDataObj()->fetch();
        
        $this->getDataObj()->clearSearchRule();
        foreach ($rs1 as $rec)
        {
            $actionRoleAccess[$rec['action_id']] = $rec;
        }
        //print_r($actionRoleAccess);
        // merge 2 rs
        for ($i=0; $i<count($rs); $i++)
        {
            $actionId = $rs[$i]['Id'];
            $rs[$i]['access_level'] = "";
            if (isset($actionRoleAccess[$actionId])) {
                $rs[$i]['access_level'] = $actionRoleAccess[$actionId]['access_level'];
            }
        }
        return $rs;
    }
    
	public function saveAccessLevel()
	{
        $roleId = $this->getRoleId();
        // read the all access_level-actionid
        $accessLevels = Openbizx::$app->getClientProxy()->getFormInputs('access_level', false);
        $actionIds = Openbizx::$app->getClientProxy()->getFormInputs('action_id', false);
        
        for ($i=0; $i<count($actionIds); $i++)
        {
            $actionId = $actionIds[$i];
            $accessLevel = $accessLevels[$i];
            // if find the record, update it, or insert a new one
            try {
                $rs = $this->getDataObj()->directFetch("[role_id]=$roleId AND [action_id]=$actionId", 1);
                if (count($rs) == 1)
                {
                    if ($rs[0]['access_level'] != $accessLevel) // update
                    {
                        $recArr = $rs[0];
                        $recArr['access_level'] = $accessLevel;
                        $this->getDataObj()->updateRecord($recArr, $rs[0]);
                    }
                }
                else    // insert
                {                	
                    if ($accessLevel !== null && $accessLevel !== "")
                    {
                        $recArr = array("role_id"=>$roleId, "action_id"=>$actionId, "access_level"=>$accessLevel);
                        $this->getDataObj()->insertRecord($recArr);
                    }
                }
            }
            catch (Openbizx\data\Exception $e) {
                $this->processDataException($e);
                return;
            }
        }
        //reload current profile
		$svcobj = Openbizx::getService(PROFILE_SERVICE);		
		$svcobj->InitProfile(Openbizx::$app->getUserProfile("username"));	
        Openbizx::$app->getClientProxy()->showClientAlert($this->getMessage("ACCESS_SAVED"));
    }
    
    protected function getRoleId()
    {
    	if ($_GET['fld:Id']) {
            $this->_roleId = $_GET['fld:Id'];
        }
        return $this->_roleId;
    }
}
