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
 * @version   $Id: PackageService.php 5038 2013-01-04 08:46:28Z hellojixian@gmail.com $
 */

use Openbizx\Openbizx;
use Openbizx\Object\MetaObject;
use Openbizx\I18n\I18n;

include_once(Openbizx::$app->getModulePath()."/common/lib/fileUtil.php");
include_once(Openbizx::$app->getModulePath()."/common/lib/httpClient.php");
include_once(Openbizx::$app->getModulePath()."/system/lib/ModuleLoader.php");

class PackageService extends MetaObject
{
    public $cacheLifeTime=0;
	
	public function discoverFeaturedApps($uri,$formParams=array())
	{
		$params['formParams'] = $formParams;
		return $this->_remoteCall($uri,'fetchFeaturedApps',$params);
	}	
	
	public function discoverRepository($uri)
	{
		return $this->_remoteCall($uri,'fetchRepoInfo');
	}
	
	public function discoverCategory($uri)
	{
		return $this->_remoteCall($uri,'fetchCategories');
	}
	
	public function discoverApplication($uri,$cat_id,$formParams=array())
	{
		if($cat_id){			
			$params['cat_id'] = $cat_id; 
		}else{
			$params['cat_id'] = null;
		}
		$params['formParams'] = $formParams;
		return $this->_remoteCall($uri,'fetchApplications',$params);
	}	
	
	public function discoverAppInfo($uri,$app_id)
	{
		if($app_id){
			$params['app_id'] = $app_id; 
		}else{
			$params['app_id'] = null;
		}		
		return $this->_remoteCall($uri,'fetchAppInfo',$params);
	}	
	
	public function discoverAppList($uri,$appIds)
	{
		if($appIds){
			$params['app_ids'] = $appIds; 
		}else{
			$params['app_ids'] = null;
		}		
		return $this->_remoteCall($uri,'fetchAppList',$params);
	}
	
	
	public function discoverAppLatestRelease($uri,$app_id)
	{
		if($app_id){
			$params['app_id'] = $app_id; 
		}else{
			$params['app_id'] = null;
		}		
		return $this->_remoteCall($uri,'fetchAppLatestRelease',$params);
	}	
	
	public function discoverNewAppRelease($uri,$timestamp)
	{
		$params['timestamp'] = $timestamp;	
		return $this->_remoteCall($uri,'fetchNewAppRelease',$params);
	}	
	
	public function discoverAppPics($uri,$app_id)
	{
		if($app_id){
			$params['app_id'] = $app_id; 
		}else{
			$params['app_id'] = null;
		}		
		return $this->_remoteCall($uri,'fetchAppPics',$params);
	}	
	
	protected function _remoteCall($uri,$method,$params=null)
    {
        $cache_id = md5($this->objectName.$uri. $method .serialize($params));         
        $cacheSvc = Openbizx::getService(CACHE_SERVICE,1);
        $cacheSvc->init($this->objectName,$this->cacheLifeTime);        		
    	if(substr($uri,strlen($uri)-1,1)!='/'){
        	$uri .= '/';
        }
        
        $uri .= "ws.php/repository/RepositoryService";            
           
        if($cacheSvc->test($cache_id) && (int) $this->cacheLifeTime>0)
        {
            $resultSetArray = $cacheSvc->load($cache_id);
        }else{
        	try{        		
		        $argsJson = urlencode(json_encode($params));
		        $lang = I18n::getCurrentLangCode();
        		$query = array(	"method=$method","format=json","argsJson=$argsJson","lang=$lang");
		        
		        $httpClient = new HttpClient('POST');
		        foreach ($query as $q)
		            $httpClient->addQuery($q);
		        $headerList = array();
		        $out = $httpClient->fetchContents($uri, $headerList);		        
		        $cats = json_decode($out, true);
		        $resultSetArray = $cats['data'];
		        $cacheSvc->save($resultSetArray,$cache_id);
        	}
        	catch(Exception $e)
        	{
        		$resultSetArray = array();
        	}
        }        
        return $resultSetArray;
    }
    
 
}
