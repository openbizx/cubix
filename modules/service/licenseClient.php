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
 * @version   $Id: licenseClient.php 3371 2012-05-31 06:17:21Z rockyswen@gmail.com $
 */

include_once(Openbizx::$app->getModulePath()."/common/lib/fileUtil.php");
include_once(Openbizx::$app->getModulePath()."/common/lib/httpClient.php");

use Openbizx\Object\MetaObject;

class LicenseClient extends MetaObject
{
    protected $_installPackage = "";
    protected $_installModules = array();
    
    public $cacheLifeTime = null;	
    
    public $repositoryUrl; // repository url
    
    function __construct(&$xmlArr)
    {
        $this->readMetadata($xmlArr);
    } 
       	
    protected function readMetadata(&$xmlArr)
    {
        parent::readMetadata($xmlArr);
    	$this->repositoryUrl = isset($xmlArr["PLUGINSERVICE"]["ATTRIBUTES"]["REPOSITORYURL"]) ? $xmlArr["PLUGINSERVICE"]["ATTRIBUTES"]["REPOSITORYURL"] : "";
    }
    
    public function acquireLicense($activationCode, $contactEmail, $serverData)
    {
        //try to process cache service.
        $argsJson = json_encode(array("activation_code"=>$activationCode,"contact_email"=>$contactEmail,"server_data"=>$serverData));
        $query = array(	"method=acquireLicense","format=json",
                        "argsJson=$argsJson");
        $httpClient = new HttpClient('POST');
        foreach ($query as $q)
            $httpClient->addQuery($q);
        $headerList = array();
        $out = $httpClient->fetchContents($this->repositoryUrl, $headerList);
        echo $out;
        $lic = json_decode($out, true);
        $licenseArray = $lic['data'];

        return $licenseArray;
    }
}

