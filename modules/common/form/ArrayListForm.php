<?php

/**
 * Openbizx Cubi Application Platform
 *
 * LICENSE http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 *
 * @package   cubi.common.form
 * @copyright Copyright (c) 2005-2011, Openbiz Technology LLC
 * @license   http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 * @link      http://code.google.com/p/openbiz-cubi/
 * @version   $Id: ArrayListForm.php 5327 2013-03-25 05:09:15Z agus.suhartono@gmail.com $
 */
use Openbizx\Openbizx;
use Openbizx\Easy\EasyForm;

class ArrayListForm extends EasyForm
{

    public $totalRecords;

    public function runSearch()
    {
        //include_once(OPENBIZ_BIN . "/easy/SearchHelper.php");
        $searchRule = "";
        foreach ($this->searchPanel as $element) {
            $searchStr = '';
            if (method_exists($element, "getSearchRule")) {
                $searchStr = $element->getSearchRule();
            } else {
                if (!$element->fieldName)
                    continue;

                $value = Openbizx::$app->getClientProxy()->getFormInputs($element->objectName);
                if ($element->fuzzySearch == "Y") {
                    $value = "*$value*";
                }
                if ($value != '') {
                    $searchStr = inputValToRule($element->fieldName, $value, $this);
                    $values[] = $value;
                }
            }
            if ($searchStr) {
                if ($searchRule == "")
                    $searchRule .= $searchStr;
                else
                    $searchRule .= " AND " . $searchStr;
            }
        }
        $this->searchRule = $searchRule;
        $this->searchRuleBindValues = $values;

        $this->isRefreshData = true;

        $this->currentPage = 1;

        Openbizx::$app->getLog()->log(LOG_DEBUG, "FORMOBJ", $this->objectName . "::runSearch(), SearchRule=" . $this->searchRule);

        $recArr = $this->readInputRecord();

        $this->searchPanelValues = $recArr;


        $this->runEventLog();
        $this->rerender();
    }

    public function fetchDataSet()
    {
        $resultRaw = $this->getRecordList();
        if (!is_array($resultRaw)) {
            return array();
        }


        $searchRule = $this->searchRule;

        preg_match_all("/\[(.*?)\]/si", $searchRule, $match);
        $i = 0;
        $searchFilter = array();
        if (is_array($this->searchRuleBindValues)) {
            foreach ($this->searchRuleBindValues as $key => $value) {
                $fieldName = $match[1][$i];
                $fieldValue = $value;
                $i++;
                $searchFilter[$fieldName] = $fieldValue;
            }
        }
        if (count($searchFilter)) {

            foreach ($resultRaw as $record) {
                $testField = false;
                foreach ($searchFilter as $field => $value) {
                    if ($record[$field] != $value) {
                        $testField = true;
                        break;
                    }
                }
                if (!$testField) {
                    $result[] = $record;
                }
            }
        } else {
            $result = $resultRaw;
        }

        //set default selected record
        if (!$this->recordId) {
            $this->recordId = $result[0]["Name"];
        }
        //set paging 
        $this->totalRecords = count($result);

        if ($this->range && $this->range > 0)
            $this->totalPages = ceil($this->totalRecords / $this->range);

        if ($this->currentPage > $this->totalPages) {
            $this->currentPage = $this->totalPages;
        }

        if (is_array($result)) {
            $result = array_slice($result, ($this->currentPage - 1) * $this->range, $this->range);
        }

        return $result;
    }

    public function getRecordList()
    {
        return array();
    }

}
