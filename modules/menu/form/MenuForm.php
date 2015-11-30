<?php
/**
 * Openbizx Cubi Application Platform
 *
 * LICENSE http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 *
 * @package   cubi.menu.form
 * @copyright Copyright (c) 2005-2011, Openbiz Technology LLC
 * @license   http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 * @link      http://code.google.com/p/openbiz-cubi/
 * @version   $Id: MenuForm.php 3364 2012-05-31 06:06:21Z rockyswen@gmail.com $
 */

use Openbizx\Openbizx;
use Openbizx\Easy\EasyForm;

class MenuForm extends EasyForm
{

    private $_parents = array();
    private $_directParentId = "";

    public function loadStatefullVars($sessCtxt)
    {
        $sessCtxt->loadObjVar($this->objectName, "DirectParent", $this->_directParentId);
        parent::loadStatefullVars($sessCtxt);
    }

    public function saveStatefullVars($sessCtxt)
    {
        $sessCtxt->saveObjVar($this->objectName, "DirectParent", $this->_directParentId);
        parent::saveStatefullVars($sessCtxt);
    }

    public function validateRequest($methodName)
    {
        $methodName = strtolower($methodName);
        if ($methodName == "listchildren")
            return true;
        return parent::validateRequest($methodName);
    }

    /**
     * Render all children records of a given record
     * @param string $id id value
     * @return void
     */
    public function ListChildren($id = '')
    {
        $this->GetParents($id);
        $this->searchRule = "[PId] = '$id'";
        $this->searchRuleBindValues = "";
        $this->clearSearchRule = true;
        return $this->rerender();
    }

    public function render()
    {

        $this->GetParents($this->_directParentId);
        $this->searchRule = "[PId] = '" . $this->_directParentId . "'";
        $this->searchRuleBindValues = "";
        $this->clearSearchRule = true;

        return parent::render();
    }

    protected function GetParents($id)
    {
        $pathArray = array();
        $this->_directParentId = $id;
        $this->fetchNodePath("[Id]='$id'", $pathArray);
        $this->_parents = $pathArray;
    }

    protected function fetchNodePath($nodeSearchRule, &$pathArray)
    {
        $recordList = $this->getDataObj()->directFetch($nodeSearchRule);
        if (count($recordList) >= 1) {

            if ($recordList[0]['PId'] != '' && $recordList[0]['PId'] != '0') {
                $searchRule = "[Id]='" . $recordList[0]['PId'] . "'";
                $this->fetchNodePath($searchRule, $pathArray);
            }
            //$node = new MenuRecord($recordList[0]);
            array_push($pathArray, $recordList[0]);
            return $pathArray;
        }
    }

    public function outputAttrs()
    {
        $output = parent::outputAttrs();
        if ($this->_directParentId && count($this->_parents) == 0)
            $this->GetParents($this->_directParentId);
        $output['parents'] = $this->_parents;
        return $output;
    }

    /**
     * Create a new record by setting correct parent id
     * @return avoid
     */
    public function newRecord()
    {
        $this->SetDisplayMode(MODE_N);
        $recArr = $this->getDataObj()->newRecord();
        if (!$recArr) {
            return $this->processDataObjError();
        }
        // add correct pid
        $recArr['PId'] = $this->_directParentId;
        $this->UpdateActiveRecord($recArr);
        return $this->rerender();
    }

    /**
     * DeleteRecord() - allow delete only if no child node
     * @return avoid
     */
    public function deleteRecord()
    {
        $rec = $this->getActiveRecord();
        if (!$rec)
            return;
        $id = $rec['Id'];
        $recordList = $this->getDataObj()->directFetch("[PId]='$id'");
        if (count($recordList) > 0) {
            $errorMsg = "Unable to delete the record that has 1 or more children nodes.";
            Openbizx::$app->getClientProxy()->showErrorMessage($errorMsg);
            return;
        }
        return parent::deleteRecord();
    }

}


