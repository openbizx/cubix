<?php

/**
 * Openbizx Cubi Application Platform
 *
 * LICENSE http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 *
 * @package   cubi.contact.form
 * @copyright Copyright (c) 2005-2011, Openbiz Technology LLC
 * @license   http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 * @link      http://code.google.com/p/openbiz-cubi/
 * @version   $Id: ContactFormGrouping.php 3356 2012-05-31 05:47:51Z rockyswen@gmail.com $
 */
use Openbizx\Openbizx;
use Openbizx\Easy\EasyFormGrouping;

class ContactFormGrouping extends EasyFormGrouping
{

    public function insertRecord()
    {
        $recArr = $this->readInputRecord();
        $this->setActiveRecord($recArr);
        if (count($recArr) == 0) {
            return;
        }

        //generate fast_index
        $svcobj = Openbizx::getService("service.chineseService");
        if ($svcobj->isChinese($recArr['first_name'])) {
            $fast_index = $svcobj->Chinese2Pinyin($recArr['first_name']);
        } else {
            $fast_index = $recArr['first_name'];
        }
        $recArr['fast_index'] = substr($fast_index, 0, 1);

        try {
            $this->ValidateForm();
        } catch (Openbizx\Validation\Exception $e) {
            $this->processFormObjError($e->errors);
            return;
        }

        $this->_doInsert($recArr);



        // in case of popup form, close it, then rerender the parent form
        if ($this->parentFormName) {
            $this->close();

            $this->renderParent();
        }

        $this->processPostAction();
    }

    public function quickSearch($start = null, $end = null)
    {
        $start = strtoupper($start);
        $end = strtoupper($end);
        $searchRule = "";
        if ($start != '' && $end != '') {
            $searchRule = "'$start'<=[fast_index] AND [fast_index]<='$end'";
        } elseif ($start) {
            $searchRule = "'$start'<[fast_index]";
        } else {
            $searchRule = "";
        }

        $this->setFixSearchRule($searchRule);
        $this->rerender();
    }

}

