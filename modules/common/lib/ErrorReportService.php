<?php

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

include_once(Openbizx::$app->getModulePath()."/common/lib/httpClient.php");

class ErrorReportService
{
	protected $reportServer;
	
  	function __construct(&$xmlArr)
   	{      
   	   $this->readMetadata($xmlArr);
   	}

   	protected function readMetadata(&$xmlArr)
   	{      
     	 $this->reportServer 	= $xmlArr["PLUGINSERVICE"]["ATTRIBUTES"]["REPORTSERVER"];      
   	}
   
   	public function report($reportData)
	{
		$params['data'] = $reportData;	
		return $this->_remoteCall('CollectErrorReport',$params);
	}	
   
	protected function _remoteCall($method,$params=null)
    {
    	$uri = $this->reportServer;
        $cache_id = md5($this->objectName.$uri. $method .serialize($params));         
        $cacheSvc = Openbizx::getService(CACHE_SERVICE,1);
        $cacheSvc->init($this->objectName,$this->cacheLifeTime);        		
    	if(substr($uri,strlen($uri)-1,1)!='/'){
        	$uri .= '/';
        }
        
        $uri .= "ws.php/udc/CollectService";            
           
        if($cacheSvc->test($cache_id) && (int) $this->cacheLifeTime>0)
        {
            $resultSetArray = $cacheSvc->load($cache_id);
        }else{
        	try{        		
		        $argsJson = urlencode(json_encode($params));
        		$query = array(	"method=$method","format=json","argsJson=$argsJson");
		        
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
