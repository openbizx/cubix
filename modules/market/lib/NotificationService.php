<?php
/**
 * Openbizx Cubi Application Platform
 *
 * LICENSE http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 *
 * @package   cubi.market.lib
 * @copyright Copyright (c) 2005-2011, Openbiz Technology LLC
 * @license   http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 * @link      http://code.google.com/p/openbiz-cubi/
 * @version   $Id: NotificationService.php 3363 2012-05-31 06:04:56Z rockyswen@gmail.com $
 */

use Openbizx\Openbizx;
use Openbizx\Object\MetaObject;

class NotificationService extends MetaObject
{
	protected $installedDO = "market.installed.do.InstalledDO";
	protected $repositoryDO = "market.repository.do.RepositoryDO";
	
	protected function getRepoInfo($uid)
	{
		$repoRec = Openbizx::getObject($this->repositoryDO,1)->fetchOne("[repository_uid]='$uid'");
		return $repoRec;
	}	
	
	public function checkNotification()
	{
		$notificationList = array();
		$msgList = $this->_checkAppUpdate();
		foreach ($msgList as $msg)
		{
			$notificationList[] = $msg;
		}
		
		$msgList = $this->_checkAppRelease();
		foreach ($msgList as $msg)
		{
			$notificationList[] = $msg;
		}
		
		
		$msgList = $this->_checkAppInfo();
		foreach ($msgList as $msg)
		{
			$notificationList[] = $msg;
		}		
		
		return $notificationList;
	}
	
	/**
	 * this function will check all installed applications 
	 * and connect to its repository server for checking updates
	 * if any installed application has update, 
	 * this function will generate a notification
	 */
	protected function _checkAppUpdate()
	{
		
		$resultSet = Openbizx::getObject($this->installedDO)->directfetch("[install_state]='OK'");
		//below code copied from appUpdateListFrom
		$repoAppsArr = array();
		$repoIdsArr = array();
		$AppsInfoArr = array();		
		if(!$resultSet)
		{
			return ;
		}
		$svc = Openbizx::getService("market.lib.PackageService");
		
		foreach ($resultSet as $record)
		{
			$repoAppsArr[$record['repository_uid']][] = $record['app_id'];
		}		
		foreach ($repoAppsArr as $repo_uid=>$apps)
		{	
			if($repo_uid){		
				$repoInfo = $this->getRepoInfo($repo_uid);
				$repo_url = $repoInfo['repository_uri'];				
				$repoIdsArr[$repo_uid]= $repoInfo['Id'];
				$appList = $svc->discoverAppList($repo_url,$apps);
				if(is_array($appList)){
					foreach ($appList as $appInfo){
						$appInfo['icon'] = $repo_url.$appInfo['icon'];
						$AppsInfoArr[$repo_uid][$appInfo['Id']] = $appInfo;
					}	
				}
			}		
		}
		$newResultSet = array();
		foreach($resultSet as $key=>$value)
		{
			$appInfo = $AppsInfoArr[$value['repository_uid']][$value['app_id']];
			$value['repo_id'] = $repoIdsArr[$value['repository_uid']]; 		
			if(is_array($appInfo)){	
				foreach($appInfo as $app_key => $app_value)
				{
					$value[$app_key] = $app_value;
				}
			}
			$newResultSet[$key] = $value;
		}
		
		foreach($newResultSet as $key=>$app)
		{			
			$current_version = $app['version'];
			$latest_version = $app['latest_version'];
			if(version_compare($current_version, $latest_version) != -1)
			{
				unset($resultSet[$key]);
			}else{
				$app['description'] = $app['version_description'];
				$resultSet[$key] = $app;
			}
		}	
		//above code copied from appUpdateListFrom
		$update_count = count($resultSet);
		if($update_count)
		{			
			$msg['type']='new_app_update';
			$msg['subject']="Found $update_count Applications Updated !";
			$msg['message']='Please notice your system administrator to update installed applications by using Openbizx Market.';
			$msg['goto_url']=OPENBIZ_APP_INDEX_URL.'/market/app_updates';
			$msg['read_access']="";			
			$msg['update_access']="Market.Manage";			
			return array($msg);
		}		
		return array();
	}
	
	/**
	 * this function will connect to installed repository server
	 * and checks if there has any update from the repository
	 */
	protected function _checkAppRelease()
	{
		$msgList = array();
		//get last check timestamp
		$checkLogRec = Openbizx::getObject("notification.do.NotificationCheckerDO")->fetchOne("[checker]='market_checker'");
		$lastCheckTime = $checkLogRec['last_checktime'];
		
		$installedRepos = Openbizx::getObject($this->repositoryDO)->directFetch();		
		foreach($installedRepos as $repo)
		{
			$repo_id = $repo['Id'];
			$repo_uri = $repo['repository_uri'];
			$svc = Openbizx::getService("market.lib.PackageService");
			$appList = $svc->discoverNewAppRelease($repo_uri,$lastCheckTime);
			$update_count = count($appList);
			if($update_count)
			{
				$msg['type']='new_app_release';
				$msg['subject']="Found $update_count New Applications Released !";
				$msg['message']='Please notice your system administrator to install applications by using Openbizx Market.';
				$msg['goto_url']=OPENBIZ_APP_INDEX_URL.'/market/applications/repo_'.$repo_id;
				$msg['read_access']="";
				$msg['update_access']="Market.Manage";			
				$msgList[] = $msg;
			}			
		}
		return $msgList;
	}
	
	/**
	 * this function will let applications sends some expire notification
	 * need to make sure this function will not being use like advertisement
	 */
	protected function _checkAppInfo()
	{
	
		$msg['type']='new_app_info';
		return array();
	}
	
}
