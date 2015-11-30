<?PHP

/**
 * Openbizx Framework
 *
 * LICENSE
 *
 * This source file is subject to the BSD license that is bundled
 * with this package in the file LICENSE.txt.
 *
 * @package   openbiz.bin.data
 * @copyright Copyright (c) 2005-2011, Rocky Swen
 * @license   http://www.opensource.org/licenses/bsd-license.php
 * @link      http://www.phpopenbiz.org/
 * @version   $Id: MenuTreeDO.php 5171 2013-01-18 10:36:59Z rockyswen@gmail.com $
 */
use Openbizx\Openbizx;
use Openbizx\Data\BizDataObj;

include_once('MenuRecord.php');

/**
 * BizDataTree class provide query for tree structured records
 *
 * @package openbiz.bin.data
 * @author Rocky Swen
 * @copyright Copyright (c) 2005-2009
 * @access public
 */
class MenuTreeDO extends BizDataObj
{

    protected $rootNodes;
    protected $depth;
    static protected $breadCrumb = null;
    static protected $fullMenuTree = null;

    public function fetchTree($rootSearchRule, $depth)
    {
        $this->fetchTreeBySearchRule($rootSearchRule, $depth);
    }

    public function fetchTreeByName($menuName, $depth)
    {
        return $this->fetchTreeBySearchRule("[name]='$menuName'", $depth);
    }

    /**
     * Fetch table as Tree structure
     * 
     * @param type $rootSearchRule
     * @param type $depth
     * @param type $globalSearchRule
     * @return type
     */
    public function fetchTreeBySearchRule($rootSearchRule, $depth, $globalSearchRule = null)
    {
       
        /* @todo need to move to outer, database not access user respond directly */ 
        /* @var $url string */
        $url = $_SERVER['REQUEST_URI'];
        
        $this->depth = $depth;
        // query on given search rule
        if ($globalSearchRule) {
            $searchRule = $rootSearchRule . " AND " . $globalSearchRule;
        } else {
            $searchRule = $rootSearchRule;
        }
        $recordList = $this->directFetch($searchRule);
        if (!$recordList) {
            $this->rootNodes = array();
            return;
        }
        $i = 0;
        foreach ($recordList as $rec) {
            $menuRec = new MenuRecord($rec);
            if ($menuRec->allowAccess()) {  // check access with role
                $this->rootNodes[$i] = $menuRec;
                if ($this->rootNodes[$i]->url == $url) {
                    $this->rootNodes[$i]->current = 1;
                }
                $i++;
            }
        }
        if ($this->depth <= 1) {
            return $this->rootNodes;
        }
        if (!$this->rootNodes) {
            return array();
        }
        foreach ($this->rootNodes as $node) {
            $this->_getChildrenNodes($node, 1, $globalSearchRule);
        }
        return $this->rootNodes;
    }

    public function fetchNodePath($nodeSearchRule, &$pathArray)
    {
        //echo "fetchNodePath($nodeSearchRule)";
        $recordList = $this->directFetch($nodeSearchRule);
        //print_r($recordList); exit;
        if (count($recordList) >= 1) {
            $i = 0;
            // find the record whose parent are not empty
            if (count($recordList) > 1) {
                for ($i = 0; $i < count($recordList); $i++) {
                    if ($recordList[$i]['PId'] != '') {
                        break;
                    }
                }
            }
            if ($recordList[$i]['PId'] != '' && $recordList[$i]['PId'] != '0') {
                $searchRule = "[Id]='" . $recordList[$i]['PId'] . "'";
                $this->fetchNodePath($searchRule, $pathArray);
            }
            $node = new MenuRecord($recordList[$i]);
            array_push($pathArray, $node);
            return $pathArray;
        }
    }

    public function getBreadCrumb()
    {
        if (self::$breadCrumb != null) {
            return self::$breadCrumb;
        }

        self::$breadCrumb = array();
        $uri = $_SERVER['REQUEST_URI'];
        if (empty($uri)) {
            return array();
        }
        $matchUri = $this->_getMatchUri($uri);
        $uri = str_replace("//", "/", str_replace(OPENBIZ_APP_INDEX_URL, '', $uri));

        $pathArray = array();

        // first find the exact uri match
        $this->fetchNodePath("[link]='$uri'", $pathArray);
        if (count($pathArray) > 0) {
            self::$breadCrumb = $pathArray;
            return $pathArray;
        }

        // then find partial match uri
        $this->fetchNodePath("[url_match] LIKE '%$matchUri%'", $pathArray);
        if (count($pathArray) > 0) {
            self::$breadCrumb = $pathArray;
            return $pathArray;
        }

        // then find partial match uri
        $this->fetchNodePath("[link] LIKE '%$matchUri%'", $pathArray);
        self::$breadCrumb = $pathArray;
        return $pathArray;
    }

    private function _getMatchUri($uri)
    {
        $matchUri = str_replace(OPENBIZ_APP_INDEX_URL, '', $uri);
        // only count first 2 parts
        $_matchUris = explode('/', $matchUri);
        if (count($_matchUris) >= 2) {
            if ($_matchUris[0] == '')
                if (count($_matchUris) >= 3) {
                    $matchUri = '/' . $_matchUris[1] . '/' . $_matchUris[2];
                } else {
                    $matchUri = $_matchUris[0] . '/' . $_matchUris[1]; 
                }
        }
        return $matchUri;
    }

    /**
     * List all children records of a given record
     *
     * @return void
     */
    private function _getChildrenNodes(&$node, $depth, $globalSearchRule = null)
    {
        $url = $_SERVER['REQUEST_URI'];
        $pid = $node->recordId;
        //echo "<br>in _getChildrenNodes";
        if ($globalSearchRule) {
            $searchRule = "[PId]='$pid' AND $globalSearchRule";
        } else {
            $searchRule = "[PId]='$pid'";
        }
        $recordList = $this->directFetch($searchRule);
        $i = 0;
        foreach ($recordList as $rec) {
            // TODO: check access with role
            $menuRec = new MenuRecord($rec);
            if ($menuRec->allowAccess()) {
                $node->childNodes[$i] = $menuRec;
                $i++;
            }
        }
        //print_r($node->childNodes);
        // reach leave node
        if ($node->childNodes == null) {
            return;
        }
        $depth++;
        // reach given depth
        if ($depth >= $this->depth) {
            return;
        } else {
            foreach ($node->childNodes as $node_c) {
                $this->_getChildrenNodes($node_c, $depth, $globalSearchRule);
            }
        }
    }

    public function recordCount($sql)
    {
        $counter = 0;
        $rs = $this->directFetch($sql);
        foreach ($rs as $record) {
            $access = $record['access'];
            if (empty($access) || Openbizx::$app->allowUserAccess($access)) {
                $counter++;
            }
        }
        return $counter;
    }

    public function directFetch($searchRule = "", $count = -1, $offset = 0, $sortRule = "")
    {
        // use menu tree cache
        $this->loadFullMenuTree();

        // search menu tree
        $searchRule = str_replace(' = ', '=', $searchRule);
        
        if (!preg_match_all("/\[([a-zA-Z0-9_]+)\]=([^ ]+)/", $searchRule, $m)) {
            return parent::directFetch($searchRule);
        }
        //echo "MenuTreeDO search rule is $searchRule";
        //print_r($m); exit;
        $n = count($m[1]);
        $hasPId = 0;
        $keyvals = array();
        for ($i = 0; $i < $n; $i++) {
            if ($m[1][$i] == 'PId') {
                $hasPId = 1;
                $PId = str_replace("'", "", $m[2][$i]);
            } else {
                $keyvals[$m[1][$i]] = str_replace("'", "", $m[2][$i]);
            }
        }
        if (!$hasPId) {
            return parent::directFetch($searchRule);
        }
        if (!$PId) {
            $PId = "__root__";
        }
        $menuItemIds = self::$fullMenuTree[$PId]['children'];
        $rs = array();
        if (empty($menuItemIds)) {
            return $rs;
        }
        foreach ($menuItemIds as $mId) {
            $rec = self::$fullMenuTree[$mId];
            $matched = true;
            foreach ($keyvals as $k => $v) {
                if ($rec[$k] != $v) {
                    $matched = false;
                    break;
                }
            }
            if ($matched) {
                $rs[] = self::$fullMenuTree[$mId];
            }
        }
        return $rs;
    }

    protected function loadFullMenuTree()
    {
        if (self::$fullMenuTree != null) {
            return;
        }
        $cache_id = 'FULL_MENU_LIST';
        $cacheSvc = Openbizx::getService(CACHE_SERVICE, 1);
        $cacheSvc->init($this->objectName, 600); // cache for 10 mins
        if ($cacheSvc->test($cache_id)) {
            self::$fullMenuTree = $cacheSvc->load($cache_id);
            return;
        }
        $rs = parent::directFetch();
        foreach ($rs as $record) {
            if (empty($record['PId'])) {
                $record['PId'] = "__root__";
            }
            unset($record['create_by']);
            unset($record['create_time']);
            unset($record['update_by']);
            unset($record['update_time']);
            unset($record['name']);
            unset($record['parent']);
            self::$fullMenuTree[$record['Id']] = $record;
        }
        foreach (self::$fullMenuTree as $mId => $record) {
            self::$fullMenuTree[$record['PId']]['children'][] = $mId;
        }
        //print_r(self::$fullMenuTree);
        $cacheSvc->save(self::$fullMenuTree, $cache_id);
        // put it in apc or file cache
    }

}
