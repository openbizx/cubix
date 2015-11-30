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
 * @version   $Id: MenuRecord.php 3364 2012-05-31 06:06:21Z rockyswen@gmail.com $
 */
use Openbizx\Openbizx;
use Openbizx\Core\Expression;
use Openbizx\I18n\I18n;

/**
 * MenuRecord class, for tree structure
 *
 * @package openbiz.bin.data
 * @author Rocky Swen
 * @copyright Copyright (c) 2005-2009
 * @since 1.2
 * @todo need to move to other package (tool, base, etc?)
 * @access public
 *
 */
class MenuRecord
{

    public $recordId;
    public $recordParentId;
    public $key;
    public $objectName;
    public $module;
    public $objectDescription;
    public $url;
    public $url_Match;
    public $target;
    public $cssClass;
    public $iconImage;
    public $iconCSSClass;
    public $access;
    public $current = 0;
    public $childNodes = null;

    /**
     * Initialize Node
     *
     * @param array $rec
     * @return void
     */
    function __construct($rec)
    {
        $this->recordId = $rec['Id'];
        $this->recordParentId = $rec['PId'];
        $this->objectName = $rec['title'];
        $this->module = $rec['module'];
        $this->objectDescription = $rec['description'];
        $this->url = $rec['link'];
        if (Expression::isExpression( $this->url ) ) {
            $this->url = Expression::evaluateExpression($this->url, $this);
        } else if (!empty($this->url)) {
            if (strpos($this->url, '/') === 0) {
                $this->url = OPENBIZ_APP_INDEX_URL . $this->url;
            } else {
                $this->url = OPENBIZ_APP_INDEX_URL . '/' . $this->url;
            }
        }
        $this->url_Match = $rec['alias'];
        //$this->cssClass = $rec['Id'];
        $this->iconImage = $rec['icon'];
        $this->iconCSSClass = $rec['icon_css'];
        $this->access = $rec['access'];

        $this->translate(); // translate for multi-language support
    }

    public function allowAccess()
    {
        $access = $this->access;
        if (!$access) {
            $access = $this->access;
        }
        if ($access) {
            return Openbizx::$app->allowUserAccess($access);
        }
        return OPENBIZ_ALLOW;
    }

    protected function translate()
    {
        $module = $this->module;
        if (!empty($this->objectName)) {
            $this->objectName = I18n::t($this->objectName, $this->getTransKey('Title'), $module);
        }
        if (!empty($this->objectDescription)) {
            $this->objectDescription = I18n::t($this->objectDescription, $this->getTransKey('Description'), $module);
        }
    }

    protected function getTransKey($name)
    {
        $k = '_MENU_' . $this->recordId . '_' . $name;
        return strtoupper($k);
    }
}
