<?php

use Openbizx\Openbizx;
use Openbizx\Core\Expression;
use Openbizx\Easy\Element\InputElement;

/**
 * 
 * {php}

  $recArr= Openbizx::$app->getSessionContext()->getVar("_OauthLogin");
  if(!$recArr && Openbizx::getService('system.lib.ModuleService')->isModuleInstalled('oauth'))
  {

  $do= Openbizx::getObject('oauth.do.OauthProviderDO');
  $sql="SELECT `type` ,  `key` ,  `value`  FROM  `{$do->mainTableName}` where status=1   LIMIT 0 , 15 ";
  $db=$do->getDBConnection();
  $recArr=$db->fetchAssoc($sql);
  Openbizx::$app->getSessionContext()->setVar("_OAUTH_{$this->type}",$recArr);
  }
  $this->assign('_OauthLogin', $recArr);
  {/php}
  <p class="input_row">
  {foreach item=do from=$_OauthLogin}
  <span><a title="{$do.type}"   href="{$app_url}/oauth_callback_handler.php?type={$do.type}&service=login" class="link_highlight" style="">{$do.type}</a> </span>
  {/foreach}

  </p>
 * @author jixian
 *
 */
class OauthLogin extends InputElement
{

    protected $redirectURL;
    protected $assocURL;

    public function readMetaData($xmlArr)
    {
        $result = parent::readMetaData($xmlArr);
        $this->redirectURL = isset($xmlArr["ATTRIBUTES"]["REDIRECTURL"]) ? $xmlArr["ATTRIBUTES"]["REDIRECTURL"] : null;
        $this->assocURL = isset($xmlArr["ATTRIBUTES"]["ASSOCURL"]) ? $xmlArr["ATTRIBUTES"]["ASSOCURL"] : null;
        return $result;
    }

    public function render()
    {
        $sHTML = "";
        if (Openbizx::getService('system.lib.ModuleService')->isModuleInstalled('oauth')) {
            $do = Openbizx::getObject('oauth.do.OauthProviderDO');
            $recArr = $do->directFetch("[status]=1", 30);
            $recArr = $recArr->toArray();

            if ($this->redirectURL) {
                $this->redirectURL = Expression::evaluateExpression($this->redirectURL, $this);
                $url_append.="redirect_url=" . urlencode($this->redirectURL) . "&";
            }
            if ($this->assocURL) {
                $this->assocURL = Expression::evaluateExpression($this->assocURL, $this);
                $url_append.="assoc_url=" . urlencode($this->assocURL) . "&";
            }
            if (count($recArr)) {
                $sHTML.= "<span class=\"oauth_bar\" $style>";
                foreach ($recArr as $oauthProvider) {
                    $url = OPENBIZ_APP_URL . "/ws.php/oauth/callback/login/type_" . $oauthProvider['type'] . '/';
                    if ($url_append) {
                        $url.= '?' . $url_append;
                    }
                    $sHTML.= "<a id=\"oauth_" . $oauthProvider['type'] . "\" title=\"" . $oauthProvider['type'] . "\"   href=\"$url\" style=\"\"></a>";
                }
                $sHTML.= "</span>";
            }
        }
        return $sHTML;
    }

}
