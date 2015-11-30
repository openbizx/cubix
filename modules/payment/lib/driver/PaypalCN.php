<?php

require_once 'Paypal.php';

class PaypalCN extends Paypal
{

    protected $providerId = 1;
    protected $type = 'paypalcn';
    protected $currencyCode = 'CNY';

}
