<?php
/**
 * Openbizx Cubi Application Platform
 *
 * LICENSE http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 *
 * @package   cubi.market.application.form
 * @copyright Copyright (c) 2005-2011, Openbiz Technology LLC
 * @license   http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 * @link      http://code.google.com/p/openbiz-cubi/
 * @version   $Id: ApplicationInstallerForm.php 4738 2012-11-14 15:27:42Z hellojixian@gmail.com $
 */

use Openbizx\Openbizx;
use Openbizx\Easy\EasyForm;

class ApplicationInstallerForm extends EasyForm 
{ 
	
	public $installState = false;
	public $installStateStr;
	public $hasUpagrade = false;
	public $appIcon;
	public $appReleaseDate;
	
	public $installDO = "market.installed.do.InstalledDO";
	
    public function validateRequest($methodName)
    {
        if ($methodName == "getProgress") return true;
        return parent::validateRequest($methodName);
    }
    

    public function outputAttrs()
    {
    	$result = parent::outputAttrs();
    	$result['remote_icon'] = $this->appIcon;
    	$result['release_date'] = $this->appReleaseDate;
    	$result['install_state'] = $this->installStateStr;
    	return $result;
    }

      
    public function fetchData()
    {
   		$RecordIds = $this->recordId;
    	$RecordIds = explode(":", $RecordIds);
   		$app_id = $RecordIds[0];
   		$repo_id = $RecordIds[1];
    	$repoRec = Openbizx::getObject("market.repository.do.RepositoryDO")->fetchOne("[status]=1 AND [Id]='$repo_id'");
    	$repo_uri = $repoRec['repository_uri'];
    	$svc = Openbizx::getService("market.lib.PackageService");
    	$result = $svc->discoverAppInfo($repo_uri,$app_id);    	
    	$this->appIcon = $repo_uri.$result['icon'];
    	$this->appReleaseDate = date('Y-m-d',strtotime($result['pkg_release_time']));
    	 
    	
    	
    	$installRec = Openbizx::getObject($this->installDO)->fetchOne("[app_id]='$app_id'");
    	if($installRec)
    	{
    		foreach($installRec as $key=>$value)
    		{
    			$result[$key]=$value;
    		}
    	}
    	
    	$result["Id"] = $this->recordId;
    	//$result['install_download'] = 0;
    	switch(strtoupper($result['install_state']))
    	{
    		default:
    		case "ERROR":
    			$result['install_progress'] = '0';
    			$result['install_download'] = '0';
    			break;
    		case "DOWNLOAD":
    			$result['install_progress'] = '20';
                if ($result['install_download_filesize'] == 0) $result['install_download'] = '0';
                else $result['install_download'] = (int)(($result['install_download'] / $result['install_download_filesize'])*100);
    			break;    			
    		case "INSTALL":
    			$result['install_progress'] = '60';
    			$result['install_download'] = '100';
    			break;
    		case "OK":
    			$result['install_progress'] = '100';
    			$result['install_download'] = '100';
    			break;	
    	}
    		
    	$result['install_state'] = $result['install_state'] ? $result['install_state'] : "Not start yet";
        $log = $result['install_log'] ? $result['install_log'] : "Click install button to start.";

        $this->installState = $this->getInstallState($repo_uri,$app_id);
        $this->hasUpagrade = $this->hasUpgrade($repo_uri,$app_id);
        if($this->hasUpagrade)
        {
        	$result['install_progress'] = '0';
    		$result['install_download'] = '0';
    		$result['install_state'] = 'Waiting';
    		$result['install_log'] = 'Click upgrade button to start';
        }
        $this->installStateStr = $result['install_state'] ;
        
    	return $result ;
    	
    }
        
    protected function getInstallState($repo_url,$app_id)
    {
    	$svc = Openbizx::getService("market.lib.InstallerService");
    	$repo_uid = $svc->getRepoUID($repo_url);
    	$searchRule = " [install_state]='OK' AND 
    					[app_id]='$app_id' AND
    					[repository_uid] = '$repo_uid'
    					";
    	$instRec = $this->getDataObj()->fetchOne($searchRule);
    	if($instRec){    		
    		return true;
    	}else{
    		return false;
    	}
    }
    
	protected function hasUpgrade($repo_url,$app_id)
    {
    	$svc = Openbizx::getService("market.lib.InstallerService");
    	$repo_uid = $svc->getRepoUID($repo_url);
    	
    	$releseInfo = $svc->discoverAppLatestRelease($repo_url,$app_id);
    	$remote_version = $releseInfo['version'];
    	
    	$searchRule = " [install_state]='OK' AND 
    					[app_id]='$app_id' AND
    					[repository_uid] = '$repo_uid'
    					";
    	$instRec = $this->getDataObj()->fetchOne($searchRule);
    	if($instRec){
    		$installed_version = $instRec['version'];
    		if(version_compare($installed_version, $remote_version) == -1 ){
    			return true;	
    		}else{
    			return false;
    		}    		
    	}else{
    		return false;
    	}
    }
    
    public function install($id)
    {
    	$RecordIds = $this->recordId;
    	$RecordIds = explode(":", $RecordIds);
   		$app_id = $RecordIds[0];
   		$repo_id = $RecordIds[1];
    	$repoRec = Openbizx::getObject("market.repository.do.RepositoryDO")->fetchOne("[status]=1 AND [Id]='$repo_id'");
    	$repo_uri = $repoRec['repository_uri'];
    	$svc = Openbizx::getService("market.lib.PackageService");
    	$result = $svc->discoverAppInfo($repo_uri,$app_id);
    	        
        $this->recordId = $id;
        try {            
            session_write_close();  // close session to unblock other ajax calls
            $packageService = "market.lib.InstallerService";
            $pkgsvc = Openbizx::getObject($packageService);
            $filename = $pkgsvc->downloadPackage($repo_uri,$app_id);
        }
        catch (Exception $e) {
            $errors = array($e->getMessage());
            $this->processFormObjError($errors);
            return;
        }
        
    }
    
    public function getProgress($id=null)
    {
    	$this->rerender();
    	return;    	
    }
    
}

