<?PHP

/**
 * Openbizx Framework
 *
 * LICENSE
 *
 * This source file is subject to the BSD license that is bundled
 * with this package in the file LICENSE.txt.
 *
 * @package   openbiz.bin.easy.element
 * @copyright Copyright &copy; 2005-2009, Rocky Swen
 * @license   http://www.opensource.org/licenses/bsd-license.php
 * @link      http://www.phpopenbiz.org/
 * @version   $Id$
 */

use Openbizx\I18n\I18n;
use Openbizx\Easy\Element\Element;
/**
 * ChartBase class is base class of all charts
 *
 * @package cubi.modules.chart.form
 * @author Rocky Swen
 * @copyright Copyright (c) 2005-2009
 * @access public
 */
class ChartElement extends Element
{

    public $chart;
    public $fieldName;
    public $data;
    public $key;
    public $attrs, $attrList;
    public $style, $styleList;
    public $value;
    public $text;

    function __construct(&$xmlArr, $formObj)
    {
        $this->formName = $formObj->objectName;
        $this->package = $formObj->package;

        $this->readMetaData($xmlArr);
    }

    /**
     * Read array meta data, and store to meta object
     *
     * @param array $xmlArr
     * @return void
     */
    protected function readMetaData(&$xmlArr)
    {
        $this->objectName = isset($xmlArr["ATTRIBUTES"]["NAME"]) ? $xmlArr["ATTRIBUTES"]["NAME"] : null;
        $this->text = isset($xmlArr["ATTRIBUTES"]["TEXT"]) ? $xmlArr["ATTRIBUTES"]["TEXT"] : null;
        $this->className = isset($xmlArr["ATTRIBUTES"]["CLASS"]) ? $xmlArr["ATTRIBUTES"]["CLASS"] : null;
        $this->fieldName = isset($xmlArr["ATTRIBUTES"]["FIELDNAME"]) ? $xmlArr["ATTRIBUTES"]["FIELDNAME"] : null;
        $this->key = isset($xmlArr["ATTRIBUTES"]["LABEL"]) ? $xmlArr["ATTRIBUTES"]["LABEL"] : null;
        $this->attrs = isset($xmlArr["ATTRIBUTES"]["ATTRS"]) ? $xmlArr["ATTRIBUTES"]["ATTRS"] : null;
        $this->style = isset($xmlArr["ATTRIBUTES"]["STYLE"]) ? $xmlArr["ATTRIBUTES"]["STYLE"] : null;
        $this->attrList = $this->parsePairs($this->attrs);
        $this->styleList = $this->parsePairs($this->style);
        $this->translate();
    }

    public function addData($data)
    {
        $this->data[] = floatval($data);
    }

    public function getChart()
    {
        if (!$this->chart) {
            $this->chart = $this->getChartObj();
        }
        return $this->chart;
    }

    public function getValue()
    {
        if ($this->getText()) {
            $value = $this->getText();
        } else {
            $value = $this->value;
        }
        return $value;
    }

    protected function getChartObj()
    {
        return new bar();
    }

    protected function parsePairs($pairString)
    {
        $pairList = array();
        if (!$pairString)
            return $pairList;
        $attrPairs = explode(";", $pairString);
        foreach ($attrPairs as $pair) {
            list($k, $v) = explode(":", $pair);
            $pairList[$k] = $v;
        }
        return $pairList;
    }

    public function render()
    {

    }

    public function reset()
    {
        
    }

    public function setValue()
    {

    }

    public function canDisplayed()
    {
        return true;
    }

    protected function translate()
    {
        $module = $this->getModuleName($this->formName);
        if (!empty($this->text))
            $this->text = I18n::t($this->text, $this->getTransKey('Text'), $module);
        if (!empty($this->label))
            $this->label = I18n::t($this->label, $this->getTransKey('Label'), $module);
        if (!empty($this->key))
            $this->key = I18n::t($this->key, $this->getTransKey('Label'), $module);
        if (!empty($this->objectDescription))
            $this->objectDescription = I18n::t($this->objectDescription, $this->getTransKey('Description'), $module);
        if (!empty($this->defaultValue))
            $this->defaultValue = I18n::t($this->defaultValue, $this->getTransKey('DefaultValue'), $module);
        if (!empty($this->elementSet))
            $this->elementSet = I18n::t($this->elementSet, $this->getTransKey('ElementSet'), $module);
    }

    protected function getTransKey($name)
    {
        $shortFormName = substr($this->formName, intval(strrpos($this->formName, '.') + 1));
        return strtoupper($shortFormName . '_' . $this->objectName . '_' . $name);
    }

    public function getModuleName($name)
    {
        return substr($name, 0, intval(strpos($name, '.')));
    }

}

