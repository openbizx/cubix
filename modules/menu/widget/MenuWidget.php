<?php

/**
 * Openbizx Cubi Application Platform
 *
 * LICENSE http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 *
 * @package   cubi.menu.widget
 * @copyright Copyright (c) 2005-2011, Openbiz Technology LLC
 * @license   http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 * @link      http://code.google.com/p/openbiz-cubi/
 * @version   $Id: MenuWidget.php 5327 2013-03-25 05:09:15Z agus.suhartono@gmail.com $
 */

use Openbizx\Openbizx;
use Openbizx\Object\MetaObject;
use Openbizx\Object\UIControlInterface;
use Openbizx\I18n\I18n;

include_once Openbizx::$app->getModulePath() . DIRECTORY_SEPARATOR . 'menu' . DIRECTORY_SEPARATOR . 'widget' . DIRECTORY_SEPARATOR . 'MenuRenderer.php';

class MenuWidget extends MetaObject implements UIControlInterface
{

    public $title;
    public $objectDescription;
    public $startMenuItem;
    public $startMenuID;
    public $searchRule;
    public $globalSearchRule;
    public $menuDeep;
    public $templateEngine;
    public $templateFile;
    public $dataObjName;
    public $cacheLifeTime;
    public $cssClass;
    protected $dataObj;

    function __construct(&$xmlArr)
    {
        $this->readMetadata($xmlArr);
        $this->translate();
    }

    protected function readMetadata(&$xmlArr)
    {
        parent::readMetaData($xmlArr);
        $this->objectName = $this->prefixPackage($this->objectName);
        $this->title = isset($xmlArr["MENUWIDGET"]["ATTRIBUTES"]["TITLE"]) ? $xmlArr["MENUWIDGET"]["ATTRIBUTES"]["TITLE"] : null;
        $this->objectDescription = isset($xmlArr["MENUWIDGET"]["ATTRIBUTES"]["DESCRIPTION"]) ? $xmlArr["MENUWIDGET"]["ATTRIBUTES"]["DESCRIPTION"] : null;
        $this->cssClass = isset($xmlArr["MENUWIDGET"]["ATTRIBUTES"]["CSSCLASS"]) ? $xmlArr["MENUWIDGET"]["ATTRIBUTES"]["CSSCLASS"] : null;
        $this->templateEngine = isset($xmlArr["MENUWIDGET"]["ATTRIBUTES"]["TEMPLATEENGINE"]) ? $xmlArr["MENUWIDGET"]["ATTRIBUTES"]["TEMPLATEENGINE"] : null;
        $this->templateFile = isset($xmlArr["MENUWIDGET"]["ATTRIBUTES"]["TEMPLATEFILE"]) ? $xmlArr["MENUWIDGET"]["ATTRIBUTES"]["TEMPLATEFILE"] : null;
        $this->startMenuItem = isset($xmlArr["MENUWIDGET"]["ATTRIBUTES"]["STARTMENUITEM"]) ? $xmlArr["MENUWIDGET"]["ATTRIBUTES"]["STARTMENUITEM"] : null;
        $this->startMenuID = isset($xmlArr["MENUWIDGET"]["ATTRIBUTES"]["STARTMENUID"]) ? $xmlArr["MENUWIDGET"]["ATTRIBUTES"]["STARTMENUID"] : null;
        $this->searchRule = isset($xmlArr["MENUWIDGET"]["ATTRIBUTES"]["SEARCHRULE"]) ? $xmlArr["MENUWIDGET"]["ATTRIBUTES"]["SEARCHRULE"] : null;
        $this->globalSearchRule = isset($xmlArr["MENUWIDGET"]["ATTRIBUTES"]["GLOBALSEARCHRULE"]) ? $xmlArr["MENUWIDGET"]["ATTRIBUTES"]["GLOBALSEARCHRULE"] : null;
        $this->menuDeep = isset($xmlArr["MENUWIDGET"]["ATTRIBUTES"]["MENUDEEP"]) ? $xmlArr["MENUWIDGET"]["ATTRIBUTES"]["MENUDEEP"] : null;
        $this->dataObjName = $this->prefixPackage($xmlArr["MENUWIDGET"]["ATTRIBUTES"]["BIZDATAOBJ"]);
        $this->cacheLifeTime = isset($xmlArr["MENUWIDGET"]["ATTRIBUTES"]["CACHELIFETIME"]) ? $xmlArr["MENUWIDGET"]["ATTRIBUTES"]["CACHELIFETIME"] : "0";
        $this->translate();
    }

    public function render()
    {
        if (!$this->allowAccess()) {
            return "";
        }
        if ($this->cacheLifeTime > 0) {
            $cache_id = md5($this->objectName);
            //try to process cache service.
            $cacheSvc = Openbizx::getService(CACHE_SERVICE, 1);
            $cacheSvc->init($this->objectName, $this->cacheLifeTime);

            if ($cacheSvc->test($cache_id)) {
                Openbizx::$app->getLog()->log(LOG_DEBUG, "MENU", "Cache Hit. menu widget name = " . $this->objectName);
                $output = $cacheSvc->load($cache_id);
            } else {
                Openbizx::$app->getLog()->log(LOG_DEBUG, "MENU", "Set cache. menu widget = " . $this->objectName);
                $output = $this->renderHTML();
                $cacheSvc->save($output, $cache_id);
            }
            return $output;
        }
        $renderedHTML = $this->renderHTML();
        return $renderedHTML;
    }

    protected function renderHTML()
    {
        $sHTML = MenuRenderer::render($this);
        return $sHTML;
    }

    public function fetchMenuTree()
    {
        $dataObj = $this->getDataObj();
        if ($this->searchRule != "") {
            $tree = $dataObj->fetchTreeBySearchRule($this->searchRule, $this->menuDeep, $this->globalSearchRule);
        } else if ($this->startMenuID != "") {
            $tree = $dataObj->fetchTree($this->startMenuID, $this->menuDeep);
        } else {
            $tree = $dataObj->fetchTreeByName($this->startMenuItem, $this->menuDeep);
        }
        return $tree;
    }

    public function outputAttrs()
    {
        $attrs = array();
        $attrs['name'] = $this->objectName;
        $attrs['title'] = $this->title;
        $attrs['css'] = $this->cssClass;
        $attrs['description'] = $this->objectDescription;
        $attrs['menu'] = $this->fetchMenuTree();
        $attrs['breadcrumb'] = $this->getDataObj()->getBreadCrumb();
        //if ($this->objectName=="menu.widget.MainTabMenu") { print_r($attrs['menu']);   print_r($attrs['breadcrumb']);  }
        return $attrs;
    }

    protected function prefixPackage($name)
    {
        // no package prefix as package.object, add it
        if ($name && !strpos($name, ".") && ($this->package)) {
            $name = $this->package . "." . $name;
        }
        return $name;
    }

    final public function getDataObj()
    {
        if (!$this->dataObj) {
            if ($this->dataObjName) {
                $this->dataObj = Openbizx::getObject($this->dataObjName, 1);
            }
            if ($this->dataObj) {
                // @todo : need inspect
                // note: maybe use parent or caller
                //$this->dataObj->bizFormName = $this->objectName;
            } else {
                //Openbizx::$app->getClientProxy()->showErrorMessage("Cannot get DataObj of ".$this->dataObjName.", please check your metadata file.");
                return null;
            }
        }
        return $this->dataObj;
    }

    public function setRequestParams()
    {
        
    }

    protected function translate()
    {
        $module = $this->getModuleName($this->objectName);
        $this->title = I18n::t($this->title, $this->getTransKey('Title'), $module);
        $this->objectDescription = I18n::t($this->objectDescription, $this->getTransKey('Description'), $module);
    }

    protected function getTransKey($name)
    {
        $shortFormName = substr($this->objectName, intval(strrpos($this->objectName, '.')) + 1);
        return strtoupper($shortFormName . '_' . $name);
    }

    public function getModuleName($name)
    {
        return substr($name, 0, intval(strpos($name, '.')));
    }

}
