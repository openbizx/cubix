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
 * @version   $Id: DataPublishingForm.php 3355 2012-05-31 05:43:33Z rockyswen@gmail.com $
 */

use Openbizx\Openbizx;

include_once (Openbizx::$app->getModulePath().'/common/form/DataSharingForm.php');
class DataPublishingForm extends DataSharingForm
{
	public function ShareRecord()
	{
		$prtForm = $this->parentFormName;
		$prtFormObj = Openbizx::getObject($prtForm);
		$recId = $this->recordId;
		$dataObj = $prtFormObj->getDataObj();
		$dataRec = $dataObj->fetchById($recId);
		
		$recArr = $this->readInputRecord();
		$DataRec = $dataRec;
		
		//notice users has new published data
		//test if changed a new owner
		if($recArr['notify_user'] && $recArr['group_perm']){
			$data = $this->fetchData();			
			$data['app_index'] = OPENBIZ_APP_INDEX_URL;
			$data['app_url'] = OPENBIZ_APP_URL;
			$data['operator_name'] = Openbizx::$app->getProfile()->getProfileName(Openbizx::$app->getUserProfile("Id"));
			
			$emailSvc = Openbizx::getService(CUBI_USER_EMAIL_SERVICE);
			
			//test if changes for group level visiable
			if($recArr['group_perm']>=1)
			{
				$group_id = $recArr['group_id'];
				$userList = $this->_getGroupUserList($group_id);
				foreach($userList as $user_id)
				{
					$emailSvc->DataPublishEmail($user_id, $data);
				}				
			}
			//test if changes for other group level visiable
			if($recArr['other_perm']>=1)
			{				
				$groupList = $this->_getGroupList();
				foreach($groupList as $group_id){								
					$userList = $this->_getGroupUserList($group_id);
					foreach($userList as $user_id)
					{
						$emailSvc->DataPublishEmail($user_id, $data);
					}				
				}
			}
		}
		
		if(isset($recArr['group_perm']))
		{
			$DataRec['group_perm'] = $recArr['group_perm'];
		}
		
		if(isset($recArr['other_perm']))
		{
			$DataRec['other_perm'] = $recArr['other_perm'];
		}
		
		if(isset($recArr['group_id']))
		{
			$DataRec['group_id']	= $recArr['group_id'];	
		}		
		
		if(isset($recArr['owner_id'])){
			$DataRec['owner_id']	= $recArr['owner_id'];
		}
		
		if($DataRec['group_perm']=='0'){
			$DataRec['other_perm']='0';
		}
		
		$DataRec->save();
		//$prtFormObj->getDataObj()->updateRecord($newDataRec,$dataRec);
		
		
		
		if($recArr['update_ref_data']){
			if($dataObj->objReferences->count()){
				$this->_casacadeUpdate($dataObj, $recArr);
			}			
		}
		
		if ($this->parentFormName)
        {
            $this->close();
            $this->renderParent();
        }
        $this->processPostAction();
	}	
}
