<?php
/**
 * Openbizx Cubi Application Platform
 *
 * LICENSE http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 *
 * @package   cubi.service
 * @copyright Copyright (c) 2005-2011, Openbiz Technology LLC
 * @license   http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 * @link      http://code.google.com/p/openbiz-cubi/
 * @version   $Id: dataPermService.php 3371 2012-05-31 06:17:21Z rockyswen@gmail.com $
 */

use Openbizx\Openbizx;

if(!defined('CUBI_GROUP_DATA_SHARE')){ define('CUBI_GROUP_DATA_SHARE','0'); }

class dataPermService
{
	
	public function CheckDataOwner($rec)
	{
		if(Openbizx::$app->allowUserAccess("data_manage.manage")){
			return true;
		}
		$user_id = Openbizx::$app->getUserProfile('Id');
		if($rec['owner_id'])
		{
			if($rec['create_by']==$user_id ||
				$rec['owner_id']==$user_id ){
				return true;
			}
		}
		else{
			if($rec['create_by']==$user_id){
				return true;
			}
		}
		return false;
	}
	
	public function CheckDataPerm($rec,$permCode,$dataObj=null)
	{
		if(Openbizx::$app->allowUserAccess("data_manage.manage")){
			return true;
		}
		if($rec==null)
		{
			return true;
		}
		$user_id = Openbizx::$app->getUserProfile('Id');
		$user_groups = Openbizx::$app->getUserProfile('groups');
		$data_owner = $rec['create_by'];
		$data_group = $rec['group_id'];
		$group_perm = $rec['group_perm'];
		$other_perm = $rec['other_perm'];

		if($rec['owner_id']!=null){
			if($user_id==$rec['owner_id']){
				return true;
			}			
		}
		if($user_id == $data_owner)
		{
			return true;
		}else{
			if(CUBI_GROUP_DATA_SHARE==0)
			{
				return false;
			}
		}		
		
		
				
		if($other_perm >= $permCode)
		{
			return true;
		}
		
		if($group_perm >= $permCode)
		{
			foreach($user_groups as $group_id)
			{
				if($group_id == $data_group)
				{
					return true;
				}
			}
		}
		
		
		//merge acl user list into this list
		$aclDO = Openbizx::getObject("common.do.DataACLDO");
		if($aclDO && $dataObj  && CUBI_DATA_ACL){
			$acl_table = $aclDO->mainTableName;
			$record_table = $dataObj->mainTableName;
			$record_id = $rec['Id'];
			$permCode = (int)$permCode;
			$searchRule = "
				[record_table]='$record_table' AND
				[record_id] = '$record_id' AND
				[user_id] = '$user_id' AND
				[user_perm] >= $permCode
			";
			$aclList = $aclDO->directfetch($searchRule);
			if(count($aclList)){
				return true;
			}
		}
		
		return false;
	}
	
	public function BuildSQLRule($dataObj,$type,$hasOwnerField=false,$alias=false)
	{
		if(Openbizx::$app->allowUserAccess("data_manage.manage")){
			return " TRUE ";
		}
		$sql_where = null;
		$user_id = Openbizx::$app->getUserProfile('Id');
		$user_groups = Openbizx::$app->getUserProfile('groups');
		
		if($hasOwnerField){
			$sql_where = " ( ([create_by]='$user_id' OR [owner_id]='$user_id') ";
		}else{
			$sql_where = " ( [create_by]='$user_id' ";
		}
		
		if(CUBI_GROUP_DATA_SHARE==0)
		{
			return $sql_where." ) ";
		}
		
		switch($type)
		{
			default:
			case 'select':
				$perm_limit = ">=1"; 				
				break;
			case 'update':
				$perm_limit = ">=2";
				break;
			case 'delete':
				$perm_limit = ">=3";
				break;
		}
				
		if(count($user_groups)){
			$sql_where .= " OR ( [group_perm] $perm_limit AND (";				
			foreach($user_groups as $group_id)
			{
				$sql_where .= " [group_id] = '$group_id' OR ";
			}
			$sql_where .= " FALSE ) )";
		}
		$sql_where .= " OR [other_perm] $perm_limit ";

		
		$aclDO = Openbizx::getObject("common.do.DataACLDO");
		if($aclDO && CUBI_DATA_ACL){			
			$acl_table = $aclDO->mainTableName;
			if($type=='select' || $alias==true)
			{
				$record_table = "T0";
			}
			else
			{
				$record_table = $dataObj->mainTableName;	
			}
			$record_main_table =$dataObj->mainTableName;	
			$record_id_field = $dataObj->getField("Id")->column;
			$sql_where .=" OR (
								SELECT COUNT(*) FROM `$acl_table` WHERE 							 
								`$acl_table`.`user_id`='$user_id' AND
								`$acl_table`.`record_table` = '$record_main_table' AND
								`$acl_table`.`record_id` = `$record_table`.`$record_id_field`
								 )";
			
		}
		$sql_where .=" )";
		return $sql_where;
	}
	
	
	public function getReadableUserList($recArr,$dataObj=null)
	{
		$recId 		= $recArr['Id'];
		$creatorId 	= $recArr['create_by'];
		$ownerId 	= $recArr['owner_id'];
		$groupId 	= $recArr['group_id'];
		$groupPerm 	= $recArr['group_perm'];
		$otherPerm 	= $recArr['other_perm'];
		
	
		$userListArr = array();
		$userListArr[$creatorId] = $creatorId;
		
		if($ownerId	!= $creatorId)
		{
			$userListArr[$ownerId] = $ownerId;
		}
			
		//test if changes for group level visiable
		if($groupPerm >=1)
		{
			$userList = $this->_getGroupUserList($groupId);
			foreach($userList as $user_id)
			{
				$userListArr[$user_id] = $user_id;
			}				
		}
		
		//test if changes for other group level visiable
		if($otherPerm >=1)
		{				
			$groupList = $this->_getGroupList();
			foreach($groupList as $group_id){
				if($groupId==$group_id)
				{
					continue;
				}					
				$userList = $this->_getGroupUserList($group_id);
				foreach($userList as $user_id)
				{
					$userListArr[$user_id] = $user_id;
				}				
			}
		}
		
		//merge acl user list into this list
		$aclDO = Openbizx::getObject("common.do.DataACLDO");
		if($aclDO && $dataObj  && CUBI_DATA_ACL){
			$acl_table = $aclDO->mainTableName;
			$record_table = $dataObj->mainTableName;
			$record_id = $recId;
			$searchRule = "
				[record_table]='$record_table' AND
				[record_id] = '$record_id' AND
				[user_perm] >=1
			";
			
			$aclList = $aclDO->directfetch($searchRule);
			foreach($aclList as $aclRec)
			{
				$user_id = $aclRec['user_id'];
				$userListArr[$user_id] = $user_id;
			}
		}
		
		return $userListArr;
		
	}
	
	public function getEditableUserList($recArr,$dataObj=null)
	{
		$recId 		= $recArr['Id'];
		$creatorId 	= $recArr['create_by'];
		$ownerId 	= $recArr['owner_id'];
		$groupId 	= $recArr['group_id'];
		$groupPerm 	= $recArr['group_perm'];
		$otherPerm 	= $recArr['other_perm'];
		
	
		$userListArr = array();
		$userListArr[$creatorId] = $creatorId;
		
		if($ownerId	!= $creatorId)
		{
			$userListArr[$ownerId] = $ownerId;
		}
			
		//test if changes for group level visiable
		if($groupPerm >=2)
		{
			$userList = $this->_getGroupUserList($groupId);
			foreach($userList as $user_id)
			{
				$userListArr[$user_id] = $user_id;
			}				
		}
		
		//test if changes for other group level visiable
		if($otherPerm >=2)
		{				
			$groupList = $this->_getGroupList();
			foreach($groupList as $group_id){
				if($groupId==$group_id)
				{
					continue;
				}					
				$userList = $this->_getGroupUserList($group_id);
				foreach($userList as $user_id)
				{
					$userListArr[$user_id] = $user_id;
				}				
			}
		}
		
		//merge acl user list into this list
		$aclDO = Openbizx::getObject("common.do.DataACLDO");
		if($aclDO && $dataObj  && CUBI_DATA_ACL){
			$acl_table = $aclDO->mainTableName;
			$record_table = $dataObj->mainTableName;
			$record_id = $recId;
			$searchRule = "
				[record_table]='$record_table' AND
				[record_id] = '$record_id' AND
				[user_perm] >=2
			";
			
			$aclList = $aclDO->directfetch($searchRule);
			foreach($aclList as $aclRec)
			{
				$user_id = $aclRec['user_id'];
				$userListArr[$user_id] = $user_id;
			}
		}
		return $userListArr;
		
	}
	protected function _getGroupList(){
		$rs = Openbizx::getObject("system.do.GroupDO")->directFetch("");
		$group_ids = array();
		foreach($rs as $group){
			$group_ids[]=$group['Id'];
		}
		return $group_ids;
	}
	
	protected function _getGroupUserList($group_id){
		$rs = Openbizx::getObject("system.do.UserGroupDO")->directFetch("[group_id]='$group_id'");
		$user_ids = array();
		foreach($rs as $user){
			$user_ids[]=$user['user_id'];
		}
		return $user_ids;
	}		
}
