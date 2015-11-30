<?php

use Openbizx\Openbizx;

require_once Openbizx::$app->getModulePath() . '/websvc/lib/WebsvcService.php';

class callbackService extends WebsvcService
{

    protected $oauthProviderDo = 'oauth.do.OauthProviderDO';

    public function __call($method, $arguments = null)
    {
        $type = Openbizx::$app->getClientProxy()->getRequestParam("type");

        $redirectURL = Openbizx::$app->getClientProxy()->getRequestParam("redirect_url");
        if ($redirectURL) {
            Openbizx::$app->getSessionContext()->setVar("oauth_redirect_url", $redirectURL);
        }

        $assocURL = Openbizx::$app->getClientProxy()->getRequestParam("assoc_url");
        if ($assocURL) {
            Openbizx::$app->getSessionContext()->setVar("oauth_assoc_url", $assocURL);
        }

        // $whitelist_arr = Openbizx::getService(CUBI_LOV_SERVICE)->getDictionary("oauth.lov.ProviderLOV(Provider)");
        $whitelist_arr = Openbizx::getObject($this->oauthProviderDo)->fetchOne("[status]=1 and [type]='{$type}'", 1);
        if ($whitelist_arr) {
            $whitelist_arr = $whitelist_arr->toArray();
        }
        if (!$whitelist_arr && !in_array($type, $whitelist_arr)) {
            throw new Exception('Unknown service');
            return;
        }

        $oatuthType = Openbizx::$app->getModulePath() . "/oauth/libs/{$type}.class.php";
        if (!file_exists($oatuthType)) {
            throw new Exception('Unknown type');
            return;
        }

        include_once $oatuthType;
        $obj = new $type;
        switch (strtolower($method)) {
            case "callback":
            case "login":
                break;
            default:
                throw new Exception('Unknown service');
                break;
        }
        return call_user_func(array($obj, $method));
    }

}
