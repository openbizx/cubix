<?php

/**
 * Openbizx Cubi Application Platform
 *
 * LICENSE http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 *
 * @package   cubi.menu.do
 * @copyright Copyright (c) 2005-2011, Openbiz Technology LLC
 * @license   http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 * @link      http://code.google.com/p/openbiz-cubi/
 * @version   $Id: MenuDataObj.php 3364 2012-05-31 06:06:21Z rockyswen@gmail.com $
 */
use Openbizx\Openbizx;
use Openbizx\Object\Statefullable;
use Openbizx\Object\MetaObject;

include_once (dirname(__FILE__) . '/MenuItemObj.php');

class MenuDataObj extends MetaObject implements Statefullable {

    public $objectName;
    public $menuTreeObj;
    public $cacheLifeTime;
    public $breadCrumb = array();
    private $rootMenuItem;

    function __construct(&$xmlArr) {
        $this->readMetadata($xmlArr);
    }

    protected function readMetadata(&$xmlArr) {
        parent::readMetaData($xmlArr);
        $this->objectName = $this->prefixPackage($this->objectName);
        $this->cacheLifeTime = isset($xmlArr["BIZDATAOBJ"]["ATTRIBUTES"]["CACHELIFETIME"]) ? $xmlArr["BIZDATAOBJ"]["ATTRIBUTES"]["CACHELIFETIME"] : "0";
        $this->rootMenuItem = $xmlArr["BIZDATAOBJ"]["MENUITEM"];
        $this->fetchEntireTree();
    }

    public function fetchEntireTree() {
        if ($this->cacheLifeTime > 0) {
            $cache_id = md5($this->objectName);
            //try to process cache service.
            $cacheSvc = Openbizx::getService(CACHE_SERVICE, 1);
            $cacheSvc->init($this->objectName, $this->cacheLifeTime);
            if ($cacheSvc->test($cache_id)) {
                Openbizx::$app->getLog()->log(LOG_DEBUG, "MENU", "Cache Hit. menu dataobj name = " . $this->objectName);
                $output = $cacheSvc->load($cache_id);
            } else {
                Openbizx::$app->getLog()->log(LOG_DEBUG, "MENU", "Set cache. menu dataobj = " . $this->objectName);
                $xmlArr = $this->rootMenuItem;
                $output = new MenuItemObj($xmlArr);
                $cacheSvc->save($output, $cache_id);
            }
            $this->menuTreeObj = $output;
        } else {
            $xmlArr = $this->rootMenuItem;
            $this->menuTreeObj = null;
            $this->menuTreeObj = new MenuItemObj($xmlArr);
        }
        $this->breadCrumb = array();
        $this->getBreadCrumb();
        return $this->menuTreeObj;
    }

    public function getBreadCrumb($node = null) {
        if (count($this->breadCrumb) > 0)
            return $this->breadCrumb;
        $url = $_SERVER['REQUEST_URI'];
        if ($node == null) {
            $node = $this->menuTreeObj;
        }
        if ($node->url == $_SERVER['REQUEST_URI']) {
            return "current";
        } elseif ($node->url_Match != "") {
            if (preg_match("@" . $node->url_Match . "@si", $_SERVER['REQUEST_URI'])) {
                return "current";
            }
        } else {
            if (is_array($node->childNodes)) {
                foreach ($node->childNodes as $name => $node) {
                    if ($this->getBreadCrumb($node) == 'current') {
                        //$node->childNodes=null;
                        array_unshift($this->breadCrumb, $node);
                        return "current";
                    }
                }
            }
        }
    }

    public function fetchTree($start_id, $deep) {
        if ($this->cacheLifeTime > 0) {
            $cache_id = md5($this->objectName . "-" . $start_id . "-" . $deep);
            //try to process cache service.
            $cacheSvc = Openbizx::getService(CACHE_SERVICE, 1);
            $cacheSvc->init($this->objectName, $this->cacheLifeTime);
            if ($cacheSvc->test($cache_id)) {
                Openbizx::$app->getLog()->log(LOG_DEBUG, "MENU", "Cache Hit. menu fetch tree, name = " . $this->objectName);
                $output = $cacheSvc->load($cache_id);
            } else {
                Openbizx::$app->getLog()->log(LOG_DEBUG, "MENU", "Set cache. menu fetch tree, name = " . $this->objectName);
                if ($start_id != "") {
                    //$this->fetchEntireTree();		
                    $tree = $this->getTreeByStartID($start_id);
                }
                $output = $this->cutTree($tree, $deep);
                $cacheSvc->save($output, $cache_id);
            }
            $tree = $output;
        } else {
            if ($start_id != "") {
                //$this->fetchEntireTree();    		
                $tree = $this->getTreeByStartID($start_id);
            }
            //$tree = $this->cutTree($tree,$deep);
        }

        return $tree->childNodes;
    }

    public function fetchTreeByName($start_item, $deep) {
        if ($this->cacheLifeTime > 0) {
            $cache_id = md5($this->objectName . "-" . $start_item . "-" . $deep);
            //try to process cache service.
            $cacheSvc = Openbizx::getService(CACHE_SERVICE, 1);
            $cacheSvc->init($this->objectName, $this->cacheLifeTime);
            if ($cacheSvc->test($cache_id)) {
                Openbizx::$app->getLog()->log(LOG_DEBUG, "MENU", "Cache Hit. menu fetch tree, name = " . $this->objectName);
                $output = $cacheSvc->load($cache_id);
            } else {
                Openbizx::$app->getLog()->log(LOG_DEBUG, "MENU", "Set cache. menu fetch tree, name = " . $this->objectName);
                if ($start_item != "") {
                    //$this->fetchEntireTree(); 		
                    $tree = $this->getTreeByStartItem($start_item);
                }
                $output = $this->cutTree($tree, $deep);
                $cacheSvc->save($output, $cache_id);
            }
            $tree = $output;
        } else {
            if ($start_item != "") {
                //$this->fetchEntireTree(); 		
                $tree = $this->getTreeByStartItem($start_item);
            }
            $tree = $this->cutTree($tree, $deep);
        }

        return $tree->childNodes;
    }

    protected function getTreeByStartItem($name, $tree = null) {
        if ($tree == null) {
            $tree = $this->menuTreeObj;
        }
        if ($tree->objectName == $name) {
            return $tree;
        } else {
            if (is_array($tree->childNodes)) {
                foreach ($tree->childNodes as $tree) {
                    $subtree = $this->getTreeByStartItem($name, $tree);
                    if ($subtree) {
                        return $subtree;
                    }
                }
            }
        }
    }

    protected function getTreeByStartID($id, $tree = null) {
        if ($tree == null) {
            $tree = $this->menuTreeObj;
        }
        if ($tree->recordId == $id) {
            return $tree;
        } else {
            if (is_array($tree->childNodes)) {
                foreach ($tree->childNodes as $tree) {
                    $subtree = $this->getTreeByStartID($id, $tree);
                    if ($subtree) {
                        return $subtree;
                    }
                }
            }
        }
    }

    protected function cutTree($tree, $deep = 1, $currentDeep = 0) {

//    		if($currentDeep>=$deep){
//    			$tree->childNodes = null;
//    			return $tree;
//    		}else{
//	    		
//	    		if(is_array($tree->childNodes)){
//	    			$currentDeep++;
//		    		foreach($tree->childNodes as $name=>$subtree){
//		    			$tree->childNodes[$name] = $this->cutTree($subtree,$deep,$currentDeep);
//		    		}
//	    		}	    		
//    		}
        return $tree;
    }

    protected function prefixPackage($name) {
        if ($name && !strpos($name, ".") && ($this->package)) // no package prefix as package.object, add it
            $name = $this->package . "." . $name;

        return $name;
    }

    public function saveStatefullVars($sessCtxt) {
        
    }

    public function loadStatefullVars($sessCtxt) {
        
    }

}
