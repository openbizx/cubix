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
 * @version   $Id: MenuRenderer.php 3487 2012-06-19 03:43:29Z hellojixian@gmail.com $
 */

/**
 * Openbizx Framework
 *
 * LICENSE
 *
 * This source file is subject to the BSD license that is bundled
 * with this package in the file LICENSE.txt.
 *
 * @package   cubi.menu.widget
 * @copyright Copyright (c) 2005-2011, Rocky Swen
 * @license   http://www.opensource.org/licenses/bsd-license.php
 * @link      http://www.phpopenbiz.org/
 * @version   $Id: MenuRenderer.php 3487 2012-06-19 03:43:29Z hellojixian@gmail.com $
 */

use Openbizx\Helpers\TemplateHelper;

/**
 * FormRenderer class is form helper for rendering form
 *
 * @package cubi.menu.widget
 * @author Rocky Swen, Jixian
 * @copyright Copyright (c) 2005-2009
 * @access public
 */
class MenuRenderer
{
    /**
     * Render widget object
     *
     * @param MenuWidget $widgetObj
     * @return string result of rendering process
     */
    static public function render($widgetObj)
    {
        $tplEngine = $widgetObj->templateEngine;
        $tplFile = TemplateHelper::getTplFileWithPath($widgetObj->templateFile, $widgetObj->package);

        if ($tplEngine == "Smarty" || $tplEngine == null) {
            return MenuRenderer::renderSmarty($widgetObj, $tplFile);
        } else {
            return MenuRenderer::renderPHP($widgetObj, $tplFile);
        }
    }

    /**
     * Render smarty template for widget object
     *
     * @param MenuWidget $widgetObj
     * @param string $tplFile
     * @return string result of rendering process
     */
    static protected function renderSmarty($widgetObj, $tplFile)
    {
        $smarty = TemplateHelper::getSmartyTemplate();  
        $attrs = $widgetObj->outputAttrs();      
        $smarty->assign("widget", $attrs);
        $smarty->assign("form", $attrs);
        $smarty->assign("formname", $widgetObj->objectName);
        $smarty->assign("module", $widgetObj->getModuleName($widgetObj->objectName));
        $smarty->assign("title", $widgetObj->title);
        $smarty->assign("errors", ( isset($widgetObj->errors) ? $widgetObj->errors : null ) ) ;
        $smarty->assign("notices", isset($widgetObj->notices) ? $widgetObj->notices : null );
        return $smarty->fetch($tplFile);
    }

    /**
     * Render PHP template for widget object
     *
     * @param MenuWidget $widgetObj
     * @param string $tplFile
     * @return string result of rendering process
     */
    static protected function renderPHP($widgetObj, $tplFile)
    {
        $view = TemplateHelper::getZendTemplate();
        $view->addScriptPath(dirname($tplFile));
        $view->widget = $widgetObj->OutputAttrs();
        /*
        $smarty->assign("formname", $widgetObj->objectName);
        $smarty->assign("module", $view->getModuleName($view->objectName));
        $smarty->assign("title", $view->title);
        $smarty->assign("errors", ( isset($view->errors) ? $view->errors : null ) );
        $smarty->assign("notices", $view->notices);
         * 
         */
        return $view->render($view->templateFile);
    }
}
