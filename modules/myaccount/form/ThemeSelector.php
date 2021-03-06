<?php

/**
 * Openbizx Cubi Application Platform
 *
 * LICENSE http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 *
 * @package   cubi.myaccount.form
 * @copyright Copyright (c) 2005-2011, Openbiz Technology LLC
 * @license   http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 * @link      http://code.google.com/p/openbiz-cubi/
 * @version   $Id: ThemeSelector.php 3365 2012-05-31 06:07:55Z rockyswen@gmail.com $
 */
use Openbizx\Easy\Element\DropDownList;

class ThemeSelector extends DropDownList
{

    function getList()
    {
        $list = array();
        $theme_dir = OPENBIZ_APP_PATH . DIRECTORY_SEPARATOR . "themes" . DIRECTORY_SEPARATOR;
        if (!is_dir($theme_dir)) {
            return array();
        }

        foreach (glob($theme_dir . DIRECTORY_SEPARATOR . "*") as $dir) {
            $theme = basename($dir);
            $themeInfo = array();
            $this->ReadThemePack($theme, $themeInfo);
            array_push($list, array("val" => $theme,
                "txt" => "<span style=\"line-height:16px;\">" . $themeInfo["name"] . "<br /> ( $theme )</span>",
                "pic" => $themeInfo["icon_url"]));
        }
        return $list;
    }

    public function ReadThemePack($theme, &$recArr = array())
    {
        $theme_dir = OPENBIZ_THEME_PATH . DIRECTORY_SEPARATOR . $theme;
        $theme_metafile = $theme_dir . DIRECTORY_SEPARATOR . "theme.xml";
        if (is_file($theme_metafile)) {
            $metadata = file_get_contents($theme_metafile);
            $xmldata = new SimpleXMLElement($metadata);
            foreach ($xmldata as $key => $value) {
                if (substr($key, 0, 1) != "@") {
                    $str = (string) $value;
                    $str = str_replace('\n', "\n", $str);
                    $str = stripcslashes($str);
                    $recArr[$key] = $str;
                }
            }
        }
        if (is_file(OPENBIZ_THEME_PATH . DIRECTORY_SEPARATOR . $theme . DIRECTORY_SEPARATOR . "images" . DIRECTORY_SEPARATOR . $recArr['icon'])) {
            $recArr['icon_url'] = OPENBIZ_THEME_URL . "/$theme/images/" . $recArr['icon'];
        } else {
            $recArr['icon_url'] = OPENBIZ_THEME_URL . "/$theme/images/spacer.gif";
        }
        if (is_file(OPENBIZ_THEME_PATH . DIRECTORY_SEPARATOR . $theme . DIRECTORY_SEPARATOR . "images" . DIRECTORY_SEPARATOR . $recArr['preview'])) {
            $recArr['preview_url'] = OPENBIZ_THEME_URL . "/$theme/images/" . $recArr['preview'];
            return $recArr;
        } else {
            $recArr['preview_url'] = OPENBIZ_THEME_URL . "/$theme/images/spacer.gif";
        }
    }

}
