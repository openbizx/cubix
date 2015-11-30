<?php

//include_once(OPENBIZ_BIN.'/easy/element/Radio.php');
use Openbizx\Easy\Element\Radio;

class ProviderSelector extends Radio
{

    public function getFromList(&$list, $selectFrom = null)
    {
        parent::getFromList($list, $selectFrom);
        foreach ($list as $key => $value) {
            $value['pic'] = OPENBIZ_RESOURCE_URL . '/payment/images/icon_' . $value['pic'] . '.png';
            $list[$key] = $value;
        }
    }

}
