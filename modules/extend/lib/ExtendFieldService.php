<?php
/**
 * Openbizx Cubi Application Platform
 *
 * LICENSE http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 *
 * @package   cubi.extend.lib
 * @copyright Copyright (c) 2005-2011, Openbiz Technology LLC
 * @license   http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 * @link      http://code.google.com/p/openbiz-cubi/
 * @version   $Id: ExtendFieldService.php 3360 2012-05-31 06:00:17Z rockyswen@gmail.com $
 */

use Openbizx\Openbizx;
use Openbizx\I18n\I18n;

class ExtendFieldService
{
	protected $extendSettingTranslationDO = "extend.do.ExtendSettingTranslationDO";
	protected $extendSettingOptionDO 		= "extend.do.ExtendSettingOptionDO";
		
	
	public function translateElemArr($elemArr,$setting_id)
	{
		$lang = I18n::getCurrentLangCode();

		if(!$lang){
			return $elemArr;
		}
		$setting_id = (int)$setting_id;
		
		$transDO = Openbizx::getObject($this->extendSettingTranslationDO,1);
		$transRec = $transDO->fetchOne("[setting_id]='$setting_id' AND [lang]='$lang'");
		if(!$transRec)
		{
			return $elemArr;
		}
		$elemArr['LABEL']		 = $transRec['label'];
		$elemArr['DESCRIPTION']	 = $transRec['description'];
		$elemArr['DEFAULTVALUE'] = $transRec['defaultvalue'];
		if($elemArr['SELECTFROM'])
		{
			$transOptDO = Openbizx::getObject($this->extendSettingOptionDO,1);
			$opts = $transOptDO->directfetch("[setting_id]='".$setting_id."' AND [lang]='$lang'");			
			if($opts && $opts->count()>0){
				$elemArr['SELECTFROM'] = $this->extendSettingOptionDO."[text:value],[setting_id]='".$setting_id."' AND [lang]='$lang' ";				
			}
		}
		return $elemArr;		
	}
}
