<?php

use Openbizx\Easy\Element\Radio;

class ActiveLogicRadio extends Radio
{

    public function getFromList(&$list, $selectFrom = null)
    {
        parent::getFromList($list, $selectFrom);
        $appInfo = $this->getFormObj()->getAppInfo();
        $trailDays = $appInfo['APP_TRAIL_DAYS'];
        if ((int) $trailDays) {
            unset($list[3]);
            $this->value = 'FREETRIAL';
        } else {
            unset($list[1]);
            $this->value = 'PURCHASE';
        }
        foreach ($list as $key => $value) {
            $value = str_replace("[strong]", "<strong>", $value);
            $value = str_replace("[/strong]", "</strong>", $value);
            $value = str_replace("[days]", $trailDays, $value);
            $list[$key] = $value;
        }
    }

}
