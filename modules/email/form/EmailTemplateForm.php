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
 * @version   $Id: EmailTemplateForm.php 3358 2012-05-31 05:57:58Z rockyswen@gmail.com $
 */

use Openbizx\Openbizx;
use Openbizx\Helpers\TemplateHelper;
use Openbizx\Object\ObjectFactoryHelper;
use Openbizx\Easy\EasyForm;

class EmailTemplateForm extends EasyForm
{

    public $configFile;
    public $configNode;
    public $modeStatus;

    protected function readMetadata(&$xmlArr)
    {
        parent::readMetaData($xmlArr);
        $this->configFile = isset($xmlArr["EASYFORM"]["ATTRIBUTES"]["CONFIGFILE"]) ? $xmlArr["EASYFORM"]["ATTRIBUTES"]["CONFIGFILE"] : null;
        $this->configNode = isset($xmlArr["EASYFORM"]["ATTRIBUTES"]["CONFIGNODE"]) ? $xmlArr["EASYFORM"]["ATTRIBUTES"]["CONFIGNODE"] : null;
    }

    public function getActiveRecord($recId = null)
    {
        if ($this->activeRecord != null) {
            if ($this->activeRecord['Id'] != null) {
                return $this->activeRecord;
            }
        }

        if ($recId == null || $recId == '') {
            $recId = Openbizx::$app->getClientProxy()->getFormInputs('_selectedId');
        }
        if ($recId == null || $recId == '') {
            return null;
        }
        $this->recordId = $recId;
        $this->fixSearchRule = "[Id]='$recId'";
        $rec = $this->fetchData();
        $this->dataPanel->setRecordArr($rec);
        $this->activeRecord = $rec;
        return $rec;
    }

    public function fetchData()
    {
        if (strtoupper($this->formType) == "NEW") {
            return $this->getNewRule();
        }

        $file = Openbizx::$app->getModulePath() . DIRECTORY_SEPARATOR . "service" . DIRECTORY_SEPARATOR . $this->configFile;
        if (!is_file($file)) {
            return;
        }
        $configArr = ObjectFactoryHelper::getXmlArray($file);
        $nodesArr = $configArr["PLUGINSERVICE"][strtoupper($this->configNode)];
        $result = array();

        preg_match("/\[(.*?)\]=\'(.*?)\'/si", $this->fixSearchRule, $match);
        $name = $match[2];

        $recordName = $configArr["PLUGINSERVICE"][strtoupper($this->configNode)]["ATTRIBUTES"]["NAME"];
        if (!$recordName) {
            for ($i = 0; $i < count($nodesArr); $i++) {
                if (is_array($nodesArr[$i]["ATTRIBUTES"])) {
                    if ($nodesArr[$i]["ATTRIBUTES"]["NAME"] == $name) {
                        foreach ($nodesArr[$i]["ATTRIBUTES"] as $key => $value) {
                            $result[$key] = $value;
                        }
                        $result["Id"] = $nodesArr[$i]["ATTRIBUTES"]["NAME"];
                        $file = Openbizx::$app->getModulePath() . DIRECTORY_SEPARATOR . "email" . DIRECTORY_SEPARATOR . "template" . DIRECTORY_SEPARATOR . $nodesArr[$i]["ATTRIBUTES"]["TEMPLATE"];
                        if (is_file($file)) {
                            $result["TEMPLATE_CONTENT"] = file_get_contents($file);
                        }
                    } else {
                        continue;
                    }
                }
            }
        } else {

            if (is_array($nodesArr["ATTRIBUTES"])) {
                if ($nodesArr["ATTRIBUTES"]["NAME"] == $name) {
                    foreach ($nodesArr["ATTRIBUTES"] as $key => $value) {
                        $result[$key] = $value;
                    }
                }
            }
            $result["Id"] = $nodesArr["ATTRIBUTES"]["NAME"];
            $file = Openbizx::$app->getModulePath() . DIRECTORY_SEPARATOR . "email" . DIRECTORY_SEPARATOR . "template" . DIRECTORY_SEPARATOR . $nodesArr["ATTRIBUTES"]["TEMPLATE"];
            if (is_file($file)) {
                $result["TEMPLATE_CONTENT"] = file_get_contents();
            }
        }
        $this->recordId = $name;
        return $result;
    }

    public function fetchDataSet()
    {
        $file = Openbizx::$app->getModulePath() . DIRECTORY_SEPARATOR . "service" . DIRECTORY_SEPARATOR . $this->configFile;
        if (!is_file($file)) {
            return;
        }

        $configArr = ObjectFactoryHelper::getXmlArray($file);
        $nodesArr = $configArr["PLUGINSERVICE"][strtoupper($this->configNode)];
        $result = array();

        $name = $configArr["PLUGINSERVICE"][strtoupper($this->configNode)]["ATTRIBUTES"]["NAME"];
        if (!$name) {
            for ($i = 0; $i < count($nodesArr); $i++) {
                if (is_array($nodesArr[$i]["ATTRIBUTES"])) {
                    foreach ($nodesArr[$i]["ATTRIBUTES"] as $key => $value) {
                        $result[$i][$key] = $value;
                    }
                }
                $result[$i]["Id"] = $nodesArr[$i]["ATTRIBUTES"]["NAME"];
            }
        } else {
            $this->fixSearchRule = "[Id]='$name'";
            $result[0] = $this->fetchData();
        }
        if (!$this->recordId) {
            $this->recordId = $result[0]["Name"];
        }
        return $result;
    }

    public function outputAttrs()
    {
        $result = parent::outputAttrs();
        $file = Openbizx::$app->getModulePath() . DIRECTORY_SEPARATOR . "service" . DIRECTORY_SEPARATOR . $this->configFile;
        if (!is_file($file)) {
            return;
        }
        $configArr = ObjectFactoryHelper::getXmlArray($file);
        $this->modeStatus = $configArr["PLUGINSERVICE"][strtoupper($this->configNode)]["ATTRIBUTES"]["MODE"];
        $result['status'] = $this->modeStatus;
        return $result;
    }

    protected function getNewRule()
    {
        $recArr = $this->readInputRecord();
        // load default values if new record value is empty
        $defaultRecArr = array();
        foreach ($this->dataPanel as $element) {
            if ($element->fieldName) {
                $defaultRecArr[$element->fieldName] = $element->getDefaultValue();
            }
        }

        foreach ($recArr as $field => $val) {
            if ($defaultRecArr[$field] != "" && $val == "") {
                $recArr[$field] = $defaultRecArr[$field];
            }
        }
        if (count($recArr) == 0) {
            $recArr = $defaultRecArr;
        }

        return $recArr;
    }

    public function InsertRecord()
    {
        $recArr = $this->readInputRecord();
        $this->setActiveRecord($recArr);
        if (count($recArr) == 0) {
            return;
        }

        try {
            $this->ValidateForm();
            $name = $recArr['NAME'];
            $this->validateErrors = array();
            if ($this->checkDupNodeName($name)) {
                $errorMessage = $this->getMessage("FORM_NODE_EXIST", array("fld_name"));
                $this->validateErrors["fld_name"] = $errorMessage;
            }
            if (count($this->validateErrors) > 0) {
                throw new Openbizx\Validation\Exception($this->validateErrors);
            }
        } catch (Openbizx\Validation\Exception $e) {
            $this->processFormObjError($e->errors);
            return;
        }
        $nodeArr = array(
            "ATTRIBUTES" => null,
            "VALUE" => null
        );
        foreach ($recArr as $key => $value) {
            $nodeArr["ATTRIBUTES"][strtoupper($key)] = $value;
        }
        $this->addNode($nodeArr);
        $this->recordId = $recArr["NAME"];
        $this->processPostAction();
    }

    public function UpdateRecord()
    {
        $recArr = $this->readInputRecord();
        $this->setActiveRecord($recArr);
        if (count($recArr) == 0)
            return;

        preg_match("/\[(.*?)\]=\'(.*?)\'/si", $this->fixSearchRule, $match);
        $name = $match[2];

        try {
            $this->ValidateForm();
        } catch (Openbizx\Validation\Exception $e) {
            $this->processFormObjError($e->errors);
            return;
        }

        $nodeArr = array(
            "ATTRIBUTES" => null,
            "VALUE" => null
        );
        foreach ($recArr as $key => $value) {
            $nodeArr["ATTRIBUTES"][strtoupper($key)] = $value;
        }
        $nodeArr["ATTRIBUTES"]["NAME"] = $name;


        $file = Openbizx::$app->getModulePath() . DIRECTORY_SEPARATOR . "email" . DIRECTORY_SEPARATOR . "template" . DIRECTORY_SEPARATOR . $recArr["TEMPLATE"];
        $content = $recArr["TEMPLATE_CONTENT"];
        file_put_contents($file, $content);

        $this->updateNode($name, $nodeArr);


        $this->recordId = $name;
        $this->processPostAction();
    }

    public function switchMode()
    {
        $file = Openbizx::$app->getModulePath() . DIRECTORY_SEPARATOR . "service" . DIRECTORY_SEPARATOR . $this->configFile;
        if (!is_file($file)) {
            return;
        }
        $configArr = ObjectFactoryHelper::getXmlArray($file);

        $this->modeStatus = $configArr["PLUGINSERVICE"][strtoupper($this->configNode)]["ATTRIBUTES"]["MODE"];
        if ($this->modeStatus == 'Enabled') {
            $status = "Disabled";
        } else {
            $status = "Enabled";
        }
        $this->modeStatus = $status;

        $configArr["PLUGINSERVICE"][strtoupper($this->configNode)]["ATTRIBUTES"]["MODE"] = $status;

        $this->saveToXML($configArr);
        $this->updateForm();
    }

    public function deleteRecord($id = null)
    {
        if ($this->resource != "" && !$this->allowAccess($this->resource . ".delete")) {
            return Openbizx::$app->getClientProxy()->redirectView(OPENBIZ_ACCESS_DENIED_VIEW);
        }

        if ($id == null || $id == '') {
            $id = Openbizx::$app->getClientProxy()->getFormInputs('_selectedId');
        }

        $selIds = Openbizx::$app->getClientProxy()->getFormInputs('row_selections', false);
        if ($selIds == null) {
            $selIds[] = $id;
        }

        //check prehabit to delete default theme
        foreach ($selIds as $id) {
            $this->removeNode($id);
        }
        if (strtoupper($this->formType) == "LIST") {
            $this->rerender();
        }

        $this->runEventLog();
        $this->processPostAction();
    }

    private function addNode($nodeArr)
    {
        $file = Openbizx::$app->getModulePath() . DIRECTORY_SEPARATOR . "service" . DIRECTORY_SEPARATOR . $this->configFile;
        if (!is_file($file)) {
            return;
        }
        $configArr = ObjectFactoryHelper::getXmlArray($file);
        $recordName = $configArr["PLUGINSERVICE"][strtoupper($this->configNode)]["ATTRIBUTES"]["NAME"];
        $recordCount = count($configArr["PLUGINSERVICE"][strtoupper($this->configNode)]);
        if (!$recordName && $recordCount) {
            array_push($configArr["PLUGINSERVICE"][strtoupper($this->configNode)], $nodeArr);
        } elseif ($recordCount) {
            $oldNodeArr = $configArr["PLUGINSERVICE"][strtoupper($this->configNode)];
            $configArr["PLUGINSERVICE"][strtoupper($this->configNode)] = array();
            array_push($configArr["PLUGINSERVICE"][strtoupper($this->configNode)], $nodeArr);
            array_push($configArr["PLUGINSERVICE"][strtoupper($this->configNode)], $oldNodeArr);
        } else {
            $configArr["PLUGINSERVICE"][strtoupper($this->configNode)] = $nodeArr;
        }
        $this->saveToXML($configArr);
    }

    private function updateNode($name, $nodeArr)
    {
        $file = Openbizx::$app->getModulePath() . DIRECTORY_SEPARATOR . "service" . DIRECTORY_SEPARATOR . $this->configFile;
        if (!is_file($file)) {
            return;
        }
        $configArr = ObjectFactoryHelper::getXmlArray($file);
        $recordName = $configArr["PLUGINSERVICE"][strtoupper($this->configNode)]["ATTRIBUTES"]["NAME"];
        if (!$recordName) {
            $nodesArr = $configArr["PLUGINSERVICE"][strtoupper($this->configNode)];
            for ($i = 0; $i < count($nodesArr); $i++) {
                if (is_array($nodesArr[$i]["ATTRIBUTES"])) {
                    if ($nodesArr[$i]["ATTRIBUTES"]["NAME"] == $name) {
                        $configArr["PLUGINSERVICE"][strtoupper($this->configNode)][$i] = $nodeArr;
                        break;
                    }
                }
            }
        } else {
            $configArr["PLUGINSERVICE"][strtoupper($this->configNode)] = $nodeArr;
        }
        $this->saveToXML($configArr);
    }

    private function removeNode($name)
    {
        $file = Openbizx::$app->getModulePath() . DIRECTORY_SEPARATOR . "service" . DIRECTORY_SEPARATOR . $this->configFile;
        if (!is_file($file)) {
            return;
        }
        $configArr = ObjectFactoryHelper::getXmlArray($file);
        $recordName = $configArr["PLUGINSERVICE"][strtoupper($this->configNode)]["ATTRIBUTES"]["NAME"];
        if (!$recordName) {
            $nodesArr = $configArr["PLUGINSERVICE"][strtoupper($this->configNode)];
            for ($i = 0; $i < count($nodesArr); $i++) {
                if (is_array($nodesArr[$i]["ATTRIBUTES"])) {
                    if ($nodesArr[$i]["ATTRIBUTES"]["NAME"] == $name) {
                        unset($configArr["PLUGINSERVICE"][strtoupper($this->configNode)][$i]);
                    }
                }
            }
        } else {
            unset($configArr["PLUGINSERVICE"][strtoupper($this->configNode)]);
        }
        $this->saveToXML($configArr);
    }

    private function checkDupNodeName($nodeName)
    {
        $file = Openbizx::$app->getModulePath() . DIRECTORY_SEPARATOR . "service" . DIRECTORY_SEPARATOR . $this->configFile;
        if (!is_file($file)) {
            return;
        }
        $configArr = ObjectFactoryHelper::getXmlArray($file);
        $recordName = $configArr["PLUGINSERVICE"][strtoupper($this->configNode)]["ATTRIBUTES"]["NAME"];
        if (!$recordName) {
            $nodesArr = $configArr["PLUGINSERVICE"][strtoupper($this->configNode)];
            $result = array();

            for ($i = 0; $i < count($nodesArr); $i++) {
                if (is_array($nodesArr[$i]["ATTRIBUTES"])) {
                    if ($nodesArr[$i]["ATTRIBUTES"]["NAME"] == $nodeName) {
                        return true;
                    }
                }
            }
        } else {
            if ($recordName == $nodeName) {
                return true;
            }
        }
        return false;
    }

    private function saveToXML($data)
    {
        $smarty = TemplateHelper::getSmartyTemplate();
        $smarty->assign("data", $data);
        $xmldata = $smarty->fetch(TemplateHelper::getTplFileWithPath("userEmailTemplate.xml.tpl", $this->package));
        $service_dir = Openbizx::$app->getModulePath() . DIRECTORY_SEPARATOR . "service";
        $service_file = $service_dir . DIRECTORY_SEPARATOR . $this->configFile;
        file_put_contents($service_file, $xmldata);
        return true;
    }

}

