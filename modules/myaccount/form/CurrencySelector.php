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
 * @version   $Id: CurrencySelector.php 3365 2012-05-31 06:07:55Z rockyswen@gmail.com $
 */

use Openbizx\I18n\I18n;
use Openbizx\Easy\Element\Listbox;

class CurrencySelector extends Listbox
{

    function getFromList(&$list)
    {

        $current_locale = I18n::getCurrentLangCode();
        //require_once('Zend/Locale.php');
        $locale = new \Zend_Locale($current_locale);

        $current_currency = CUBI_DEFAULT_CURRENCY;
        if (!$current_currency) {
            $current_currency = "USD";
        }
        //require_once('Zend/Currency.php');

        $currency = new \Zend_Currency($current_currency, $current_locale);
        $currencyList = $currency->getCurrencyList();
        foreach ($currencyList as $currency_code => $country) {

            $display_name = $currency->getName($currency_code, $current_locale);
            if ($display_name) {
                array_push($list, array("val" => $currency_code,
                    "txt" => "$currency_code - $display_name"
                ));
            }
        }
        return $list;
    }

}
