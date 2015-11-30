<?php

use Openbizx\Openbizx;
use Openbizx\Easy\Element\Element;

class OauthSelector extends Element
{

    public function render()
    {
        $sHTML = "";
        if (Openbizx::getService('system.lib.ModuleService')->isModuleInstalled('oauth')) {
            $do = Openbizx::getObject('oauth.do.OauthProviderDO');
            $recArr = $do->directFetch("[status]=1", 30);
            $recArr = $recArr->toArray();
            if (count($recArr)) {
                $sHTML.= "<span class=\"oauth_list\" $style>";
                foreach ($recArr as $oauthProvider) {
                    $img = "<img src=\"" . OPENBIZ_RESOURCE_URL . '/oauth/images/oauth_logo_' . $oauthProvider['type'] . ".png\" />";
                    $sHTML.= "<a id=\"oauth_" . $oauthProvider['type'] . "\" title=\"" . $oauthProvider['type'] . "\"   href=\"" . OPENBIZ_APP_URL . "/ws.php/oauth/callback/login/type_" . $oauthProvider['type'] . "\" style=\"\">$img</a>";
                }
                $sHTML.= "</span>";
            }
        }
        return $sHTML;
    }

}
