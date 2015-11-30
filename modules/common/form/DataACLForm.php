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
 * @version   $Id: DataACLForm.php 3355 2012-05-31 05:43:33Z rockyswen@gmail.com $
 */

use Openbizx\Openbizx;

require_once('DataSharingForm.php');
class DataACLForm extends DataSharingForm
{
	public $aclDO = "common.do.DataACLDO";
	
	public function SetPrtRecordId($id)
	{		
		return;
	}
	
	public function fetchDataSet()
	{
		$prtRecord = $this->fetchData();
		$this->editable = $prtRecord['editable'];
		
		$prtForm = $this->parentFormName;
		$prtFormObj = Openbizx::getObject($prtForm);
		
		$record_table = $prtFormObj->getDataObj()->mainTableName;
		$record_id = $this->parentRecordId;
		$this->searchRule = "[record_table]='$record_table' AND [record_id]='$record_id'";
		$result =  parent::fetchDataSet();
		return $result;
	}
	
	
	public function addAcl()
	{
		$inputs = $this->readInputs();
		$acl_user = $inputs['fld_acl_uid'];
		$acl_perm = $inputs['fld_acl_perm'];
		
		//get UserID
		$userRec = Openbizx::getObject("system.do.UserDO",1)->fetchOne("[username]='$acl_user'");
		$acl_user_id = $userRec['Id'];
		
		$parent_record_id = $this->parentRecordId;
		
		//get parent do table
		$prtForm = $this->parentFormName;
		$prtFormObj = Openbizx::getObject($prtForm);
		$dataObj = $prtFormObj->getDataObj();
		$parent_record_table = $dataObj->mainTableName;
		
		$aclDO = Openbizx::getObject("common.do.DataACLDO");
		$sql = "
			[record_table]='$parent_record_table' AND 
			[record_id]='$parent_record_id' AND
			[user_id]='$acl_user_id' 
		";
		$rec = $aclDO->fetchOne($sql);
		
		if(!$rec){		
			$aclRecord = array(
				"record_table" => $parent_record_table,
				"record_id" => $parent_record_id,
				"user_id"	=> $acl_user_id,
				"user_perm"	=> $acl_perm
			);
			$aclDO->insertRecord($aclRecord);
		}
		
		$this->rerender();
	}
	
	
}
