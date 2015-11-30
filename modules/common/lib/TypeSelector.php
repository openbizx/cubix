<?php

/**
 * Openbizx Cubi Application Platform
 *
 * LICENSE http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 *
 * @package   cubi.common.lib
 * @copyright Copyright (c) 2005-2011, Openbiz Technology LLC
 * @license   http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 * @link      http://code.google.com/p/openbiz-cubi/
 * @version   $Id: TypeSelector.php 3621 2012-07-13 13:21:30Z hellojixian@gmail.com $
 */

use Openbizx\Openbizx;
use Openbizx\Easy\Element\DropDownList;

class TypeSelector extends DropDownList
{

    protected function renderList()
    {

        if ($this->formPrefix) {
            $formNameStr = str_replace(".", "_", $this->getFormObj()->objectName) . "_";
        }
        $onchange_func = $this->getOnChangeFunction();
        $list = $this->getList();

        if ($this->blankOption) { // ADD a blank option
            $entry = explode(",", $this->blankOption);
            $text = $entry[0];
            $value = ($entry[1] != "") ? $entry[1] : null;
            $entryList = array(array("val" => $value, "txt" => $text));
            $list = array_merge($entryList, $list);
        }

        $value = $this->value ? $this->value : $this->getText();
        $sHTML = "<div  class=\"dropdownlist\"  id=\"" . $formNameStr . $this->objectName . "_scroll\" style=\"display:none;\">" .
                $sHTML .= "<ul style=\"display:none;z-index:50\" id=\"" . $formNameStr . $this->objectName . "_list\">";
        $theme = Openbizx::$app->getCurrentTheme();
        $image_url = OPENBIZ_THEME_URL . "/" . $theme . "/images";

        foreach ($list as $item) {
            $val = $item['val'];
            $txt = $item['txt'];
            $pic = $item['pic'];
            if ($pic != "") {
                $str_pic = "<img src='" . $image_url . "/spacer.gif' style='background-color:#$pic;width:12px;height:12px;padding-top:0px;margin-top:3px;' />";
            } else {
                $str_pic = "";
            }
            if (!preg_match("/</si", $txt)) {
                $display_value = $txt;
            } else {
                $display_value = $val;
            }
            if ($str_pic) {
                $li_option_value = $str_pic . "<span>" . $txt . "</span>";
            } else {
                $li_option_value = $txt;
            }
            if ($val == $value) {
                $option_item_style = " class='selected' ";
            } else {
                $option_item_style = " onmouseover=\"this.className='hover'\" onmouseout=\"this.className=''\" ";
            }

            $sHTML .= "<li $option_item_style
				onclick=\"$('" . $formNameStr . $this->objectName . "_list').hide();
							$('" . $formNameStr . $this->objectName . "_scroll').hide();
							$('" . $formNameStr . $this->objectName . "').setValue('" . addslashes($display_value) . "');
							$('" . $formNameStr . $this->objectName . "_hidden').setValue('" . addslashes($val) . "');
							$('span_" . $formNameStr . $this->objectName . "').innerHTML = this.innerHTML;
							$onchange_func ;
							$('" . $formNameStr . $this->objectName . "').className='" . $this->cssClass . "'
							\"					
				>$li_option_value</li>";

            if ($val == $value) {
                $this->defaultDisplayValue = "" . $str_pic . "<span>" . $txt . "</span>";
            }
        }
        $sHTML .= "</ul>";
        $sHTML .= "</div>";
        return $sHTML;
    }

}
