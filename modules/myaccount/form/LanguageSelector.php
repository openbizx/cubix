<?php

/**
 * Openbizx Cubi Application Platform
 *
 * LICENSE http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 *
 * @package   cubi.myaccount.form
 * @copyright Copyright (c) 2005-2011, Openbiz Technology LLC
 * @license   http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 * @link      http://code.google.com/p/openbiz-cubi/
 * @version   $Id: LanguageSelector.php 3365 2012-05-31 06:07:55Z rockyswen@gmail.com $
 */

use Openbizx\I18n\I18n;
use Openbizx\Easy\Element\DropDownList;

class LanguageSelector extends DropDownList
{

    function getList()
    {
        $list = array();
        $lang_dir = OPENBIZ_APP_PATH . DIRECTORY_SEPARATOR . "languages" . DIRECTORY_SEPARATOR . $lang;
        if (!is_dir($lang_dir)) {
            return array();
        }
        $current_locale = I18n::getCurrentLangCode();
        //require_once('Zend/Locale.php');
        $locale = new \Zend_Locale($current_locale);
        $code2name = $locale->getTranslationList('language', $locale);
        foreach (glob($lang_dir . DIRECTORY_SEPARATOR . "*") as $dir) {
            $lang_code = basename($dir);
            if ($lang_code == 'dictionary') {
                continue;
            }
            $locale = explode('_', $lang_code);
            $lang_name = $code2name[strtolower($locale[0])];
            array_push($list, array("val" => $lang_code,
                "txt" => $lang_name . " ( $lang_code )",
                "pic" => OPENBIZ_APP_URL . "/images/nations/22x14/" . strtolower($locale[1]) . ".png"));
        }
        return $list;
    }

}
