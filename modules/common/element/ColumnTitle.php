<?php
/**
 * Openbizx Cubi Application Platform
 *
 * LICENSE http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 *
 * @package   cubi.common.element
 * @copyright Copyright (c) 2005-2011, Openbiz Technology LLC
 * @license   http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 * @link      http://code.google.com/p/openbiz-cubi/
 * @version   $Id: ColumnTitle.php 3355 2012-05-31 05:43:33Z rockyswen@gmail.com $
 */

use Openbizx\Easy\Element\ColumnText;

class ColumnTitle extends ColumnText
{
	public function getIDPrefix()
	{
		$rec = $this->getFormObj()->getActiveRecord();
		
		$id = $rec["Id"];
		if(!$id && $this->fieldName=='Id'){
			$id = $this->value;
		}
		$id_display = "<span class=\"title_id\" >$id</span>";
		return $id_display;
	}
	
	public function render(){
		$sHTML = parent::render();
		if($this->fieldName!='Id'){
			$sHTML = $this->getIDPrefix().$sHTML;
		}
		else{
			$sHTML = $this->getIDPrefix();
		}
		return $sHTML;
	}
}
