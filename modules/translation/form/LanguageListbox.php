<?php

/**
 * Openbizx Cubi Application Platform
 *
 * LICENSE http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 *
 * @package   cubi.translation.form
 * @copyright Copyright (c) 2005-2011, Openbiz Technology LLC
 * @license   http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 * @link      http://code.google.com/p/openbiz-cubi/
 * @version   $Id: LanguageListbox.php 3374 2012-05-31 06:22:06Z rockyswen@gmail.com $
 */
use Openbizx\Openbizx;
use Openbizx\I18n\I18n;
use Openbizx\Easy\Element\EditCombobox;

class LanguageListbox extends EditCombobox
{

    public function getFromList(&$list)
    {
        $current_locale = I18n::getCurrentLangCode();

        $country = Openbizx::$app->getClientProxy()->getFormInputs("fld_region");
        $country = strtoupper($country);
        if (!$country) {
            $locale = explode('_', $current_locale);
            $country = strtoupper($locale[0]);
        }
        //require_once('Zend/Locale.php');
        $locale = new \Zend_Locale($current_locale);
        $code2name = $locale->getTranslationList('territorytolanguage', $locale);
        $list = array();
        $i = 0;
        foreach ($code2name as $key => $value) {

            if (preg_match('/' . $country . '/', $value) || strtoupper($key) == $country) {
                $lang_list = explode(" ", $value);
                foreach ($lang_list as $lang) {
                    $list[$i]['txt'] = strtolower($key) . "_" . strtoupper($lang);
                    $list[$i]['val'] = strtolower($key) . "_" . strtoupper($lang);
                    $i++;
                }
            }
        }
        return $list;
    }

}
