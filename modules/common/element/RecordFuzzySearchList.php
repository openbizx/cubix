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
 * @version   $Id: RecordFuzzySearchList.php 3355 2012-05-31 05:43:33Z rockyswen@gmail.com $
 */

use Openbizx\Easy\Element\AutoSuggest;

use Openbizx\Openbizx;

class RecordFuzzySearchList extends AutoSuggest
{

    public $searchFields;

    public function readMetaData(&$xmlArr)
    {
        parent::readMetaData($xmlArr);
        $this->searchFields = isset($xmlArr["ATTRIBUTES"]["SEARCHFIELDS"]) ? $xmlArr["ATTRIBUTES"]["SEARCHFIELDS"] : null;
    }

    public function getSearchRule()
    {
        $value = Openbizx::$app->getClientProxy()->getFormInputs($this->objectName);
        $value = addslashes($value); //escape sql strings

        if ($value != '') {
            $searchStr = " [$this->fieldName] LIKE '%$value%' ";
        } else {
            return "";
        }

        if ($this->searchFields) { //process other search fields
            $fields = $lovService = Openbizx::getService(CUBI_LOV_SERVICE)->getList($this->searchFields);
            foreach ($fields as $opt) {
                $field = $opt['val'];
                $searchStr.= " OR [$field] LIKE '%$value%' ";
            }
        }

        $searchStr = "( $searchStr )";
        return $searchStr;
    }

}

