<?php

use Openbizx\Openbizx;
use Openbizx\Easy\EasyForm;

class FinishedForm extends EasyForm
{

    public function fetchData()
    {
        $result = Openbizx::getService("payment.lib.PaymentService")->getReturnData($_GET['type']);
        $txn_id = $result['txn_id'];
        $verify = Openbizx::getService("payment.lib.PaymentService")->validateNotification($_GET['type'], $txn_id);
        return $result;
    }

}
