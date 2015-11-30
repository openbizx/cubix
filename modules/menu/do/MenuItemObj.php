<?php
/**
 * Openbizx Cubi Application Platform
 *
 * LICENSE http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 *
 * @package   cubi.menu.do
 * @copyright Copyright (c) 2005-2011, Openbiz Technology LLC
 * @license   http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 * @link      http://code.google.com/p/openbiz-cubi/
 * @version   $Id: MenuItemObj.php 3364 2012-05-31 06:06:21Z rockyswen@gmail.com $
 */

use Openbizx\Core\Expression;

class MenuItemObj
{
 	public $recordId;
 	public $recordParentId;   
    public $objectName;   
    public $objectDescription;
    public $url;
    public $url_Match;
	public $target;
	public $cssClass;
	public $iconImage;
	public $iconCSSClass;
	public $childNodes = null;
	 
    function __construct($xmlArr, $pid="0")
    {
    	$this->recordParentId		 = $pid;
    	$this->readMetadata($xmlArr);
    }
    
    function readMetadata($xmlArr){
        $this->recordId		 	 = $xmlArr["ATTRIBUTES"]["ID"];        
    	$this->objectName         = $xmlArr["ATTRIBUTES"]["NAME"];
        $this->objectDescription = $xmlArr["ATTRIBUTES"]["DESCRIPTION"];
        $this->url		 = $xmlArr["ATTRIBUTES"]["URL"];
        $this->url		 = Expression::evaluateExpression($this->url, $this);
        $this->url_Match	 = $xmlArr["ATTRIBUTES"]["URLMATCH"];
        $this->target		 = $xmlArr["ATTRIBUTES"]["TARGET"];        
        $this->cssClass		 = $xmlArr["ATTRIBUTES"]["CSSCLASS"];
        $this->iconImage		 = $xmlArr["ATTRIBUTES"]["ICONIMAGE"];
        $this->iconCSSClass		 = $xmlArr["ATTRIBUTES"]["ICONCSSCLASS"];
        if(is_array($xmlArr["MENUITEM"])){
        	$this->childNodes = array();
        	if(isset($xmlArr["MENUITEM"]["ATTRIBUTES"])){
        		$this->childNodes[$xmlArr["MENUITEM"]["ATTRIBUTES"]["ID"]] = new MenuItemObj($xmlArr["MENUITEM"],$this->recordId);
        	}else{        	
	        	foreach($xmlArr["MENUITEM"] as $menuItem){
	        		$this->childNodes[$menuItem["ATTRIBUTES"]["ID"]] = new MenuItemObj($menuItem,$this->recordId);
	        	}
        	}
        }
    	
    }
    
    
    
}
