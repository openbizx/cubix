<?php

use Openbizx\Openbizx;

require_once Openbizx::$app->getModulePath() . '/websvc/lib/WebsvcService.php';

class callbackService extends WebsvcService
{

    protected $svc = "payment.lib.PaymentService";

    public function verify()
    {
        $this->_logRequest();
        return Openbizx::getObject($this->svc)->ValidateNotification($_REQUEST['type']);
    }

    protected function _logRequest()
    {
        $logfile = OPENBIZ_LOG_PATH . DIRECTORY_SEPARATOR . 'PaymentWebSvc.log';
        foreach ($_REQUEST as $key => $value) {
            $logStr .= "$key => $value \n";
        }
        $fp = fopen($logfile, 'a');
        fwrite($fp, $logStr . "\n\n");
        fclose($fp); // close file
        chmod($logfile, 0600);
        return;
    }

}
