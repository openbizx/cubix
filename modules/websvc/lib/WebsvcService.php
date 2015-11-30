<?php
/**
 * Openbizx Cubi Application Platform
 *
 * LICENSE http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 *
 * @package   cubi.websvc.lib
 * @copyright Copyright (c) 2005-2011, Openbiz Technology LLC
 * @license   http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 * @link      http://code.google.com/p/openbiz-cubi/
 * @version   $Id: WebsvcService.php 4420 2012-10-08 09:09:31Z hellojixian@gmail.com $
 */


use Openbizx\Openbizx;
use Openbizx\Object\MetaIterator;
use Openbizx\Helpers\MessageHelper;

include_once 'WebsvcError.php';
include_once 'WebsvcResponse.php';


class WebsvcService
{   
    public $errorCode = 0;
    public $websvcDO = "websvc.do.WebsvcDO";
    public $publicMethods;
    public $messageFile;
    public $objectMessages;
    public $requireAuth = "N";

    function __construct(&$xmlArr)
    {      
        $this->readMetadata($xmlArr);
    }

    protected function readMetadata(&$xmlArr)
    {      
        $this->requireAuth = isset($xmlArr["PLUGINSERVICE"]["ATTRIBUTES"]["REQUIREAUTH"]) ? $xmlArr["PLUGINSERVICE"]["ATTRIBUTES"]["REQUIREAUTH"] : 'N';
        $this->requireAuth = strtoupper($this->requireAuth);
        $this->publicMethods = new MetaIterator($xmlArr["PLUGINSERVICE"]["PUBLICMETHOD"],"PublicMethod",$this);
        $this->messageFile = isset($xmlArr["PLUGINSERVICE"]["ATTRIBUTES"]["MESSAGEFILE"]) ? $xmlArr["PLUGINSERVICE"]["ATTRIBUTES"]["MESSAGEFILE"] : null;
        $this->objectMessages = MessageHelper::loadMessage($this->messageFile);
    }
/*
      - authenticate($api_key, $secret)
      - checkAccess($api_key, $secret, $method)
      - invoke($method, $params)
      - printOutput($format, $response)
      - Error_Code
*/
    public function invoke()
    {
        $username = $this->getInput('username');
        $api_key = $this->getInput('api_key');
        $secret = $this->getInput('secret');
        $format = $this->getInput('format');
        
        if($this->requireAuth=='Y'){
	        if ($this->authenticate($username, $api_key, $secret) == false) {
	            $this->output(null, $format);
	            return;
	        }
        }
        
        $service = $this->getInput('service');
        $method = $this->getInput('method');
        
        if ($this->checkAccess($username, $method) == false) {
            $this->output(null, $format);
            return;
        }
        
        // read inputs
        $args = $this->getInputArgs('args');
        
        // call function
        if(is_array($args)){
        	$response = call_user_func_array(array($this, $method), $args);
        }
        else
        {        	 
        	$response = $this->$method();
        }
        $this->output($response, $format);
    }
    
    protected function getInput($name)
    {
        $val = isset($_REQUEST[$name]) ? $_REQUEST[$name] : null;
        return $val;
    }
    
    protected function getInputArgs()
    {
        if (isset($_POST['argsJson'])) {
            $argsJson = $_POST['argsJson'];
            $args = json_decode($argsJson, true);
            return $args;
        }
        // read 'arg_name' or 'argsJson'
        $args = array();
        foreach ($_POST as $name=>$val) {
            if (strpos($name, 'arg_') === 0) {
                list($arg, $key) = explode('_', $name);
                $args[$key] = $val;
            }
        }
        return $args;
    }
    
    protected function authenticate($username, $api_key, $secret=null)
    {
        $websvcDO = Openbizx::getObject($this->websvcDO);
        $searchRule = "[username]='$username' AND [api_key]='$api_key'";
        if ($secret)
            $searchRule .= " AND [secret]='$secret'";
        $record = $websvcDO->fetchOne($searchRule);
        if (!$record) {
            $this->errorCode = WebsvcError::INVALID_APIKEY;
            return false;
        }
        return true;        
    }
    
    /*
      <Service Name=...>
      <PublicMethod Name=... Access=.../>
      <PublicMethod Name=... Access=.../>
    */
    protected function checkAccess($username, $method)
    {
        // check if the method is defined in public methods
        $validMethod = false;
        foreach ($this->publicMethods as $pmethod)
        {
            if (strtolower($method) == strtolower($pmethod->objectName)) {
                $validMethod = true;
                break;
            }
        }
        if (!$validMethod) {
            $this->errorCode = WebsvcError::INVALID_METHOD;
            return false;
        }
        
        $access = $pmethod->access;
        return $this->checkPermission($username, $access);
    }
    
    protected function checkPermission($username, $access)
    {
        if (!$access) return true;
        // check user ACL 
        // load user profile first and check profile against public method Access
        $profileSvc = Openbizx::getService(PROFILE_SERVICE);
        $profile = $profileSvc->InitProfile($username);
        //echo $access; print_r($profile); exit;
        $aclSvc = Openbizx::getService(ACL_SERVICE);
        if (!$aclSvc->checkUserPerm($profile, $access)) {
            $this->errorCode = WebsvcError::NOT_AUTH;
            return false;
        }
        return true;
    }
    
    /**
     * 
     * output result to remtoe client 
     * @param unknown_type $response
     * @param unknown_type $format
     * @param String  $checksumKey  remote client may use this key to validate response data, this logic has been used in app cloud cluster countrol
     */
    protected function output($response=null, $format='xml', $checksumKey = null)
    {
        $errMsg = WebsvcError::getErrorMessage($this->errorCode);
        $wsResp = new WebsvcResponse();
        $wsResp->setChecksumKey($checksumKey);
        $wsResp->setError($this->errorCode, $errMsg);
        $wsResp->setData($response);
        $wsResp->output($format);
    }
}

class PublicMethod
{
    public $objectName;
    public $access;

    /**
     * Contructor, store form info from array to variable of class
     *
     * @param array $xmlArr array of form information
     */
    public function __construct($xmlArr)
    {
        $this->objectName = $xmlArr["ATTRIBUTES"]["NAME"];
        $this->access = $xmlArr["ATTRIBUTES"]["ACCESS"];
    }
}
