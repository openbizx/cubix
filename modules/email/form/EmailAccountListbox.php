<?php
/**
 * Openbizx Cubi Application Platform
 *
 * LICENSE http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 *
 * @package   cubi.email.form
 * @copyright Copyright (c) 2005-2011, Openbiz Technology LLC
 * @license   http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 * @link      http://code.google.com/p/openbiz-cubi/
 * @version   $Id: EmailAccountListbox.php 3358 2012-05-31 05:57:58Z rockyswen@gmail.com $
 */

use Openbizx\Object\ObjectFactoryHelper;
use Openbizx\Easy\Element\Listbox;

class EmailAccountListbox extends Listbox{
	
	public $configFile = "emailService.xml";
	public $configNode = "Account";
	
	public function getFromList(&$list)
    {    	
   	    
    	$file = Openbizx::$app->getModulePath().DIRECTORY_SEPARATOR."service".DIRECTORY_SEPARATOR.$this->configFile;
		if(!is_file($file)){
			return;
		}
		
		$configArr=ObjectFactoryHelper::getXmlArray($file);
		$nodesArr = $configArr["PLUGINSERVICE"]["ACCOUNTS"][strtoupper($this->configNode)];
		
    	
		$list = array();
		$i=0;

    	$name = $configArr["PLUGINSERVICE"]["ACCOUNTS"][strtoupper($this->configNode)]["ATTRIBUTES"]["NAME"];
		if(!$name){
			for($i=0;$i<count($nodesArr);$i++){
				$list[$i]["txt"]=$nodesArr[$i]["ATTRIBUTES"]["NAME"]." ( ".$nodesArr[$i]["ATTRIBUTES"]["FROMEMAIL"]." )";
				$list[$i]["val"]=$nodesArr[$i]["ATTRIBUTES"]["NAME"];
			}
			
		}else{
			$list[0]["txt"]=$name." ( ".$configArr["PLUGINSERVICE"]["ACCOUNTS"][strtoupper($this->configNode)]["ATTRIBUTES"]["FROMEMAIL"]." )";
			$list[0]["val"]=$name;
		}		
		
		return $list;
    }
}
