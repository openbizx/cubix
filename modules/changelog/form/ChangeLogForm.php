<?php

/**
 * Openbizx Cubi Application Platform
 *
 * LICENSE http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 *
 * @package   cubi.changelog.form
 * @copyright Copyright (c) 2005-2011, Openbiz Technology LLC
 * @license   http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 * @link      http://code.google.com/p/openbiz-cubi/
 * @version   $Id$
 */

use Openbizx\Openbizx;
use Openbizx\I18n\I18n;
use Openbizx\Helpers\MessageHelper;
use Openbizx\Data\DataRecord;

use Openbizx\Easy\EasyForm;

class ChangeLogForm extends EasyForm
{

    protected $logDO = "changelog.do.ChangeLogDO";
    protected $changeLogMessages;
    protected $isShowComment;

    protected function readMetadata(&$xmlArr)
    {
        $this->isShowComment = $xmlArr["EASYFORM"]["ATTRIBUTES"]["SHOWCOMMENT"] ? $xmlArr["EASYFORM"]["ATTRIBUTES"]["SHOWCOMMENT"] : 'Y';
        //load message file
        $this->changeLogMessages = MessageHelper::loadMessage("changelog.ini", "changelog");

        if (strtolower($xmlArr["EASYFORM"]["ATTRIBUTES"]["FORMTYPE"]) == 'edit' &&
                $this->isShowComment != 'N'
        ) {
            //add a comment field on fly
            $elem_comment_attrs = array(
                "NAME" => 'fld_changelog_comment',
                "CLASS" => 'Textarea',
                "ELEMENTSET" => "Change Comment",
                "LABEL" => $this->getChangeLogMessage(CHENGLOG_LABEL),
                "DESCRIPTION" => $this->getChangeLogMessage(CHENGLOG_DESC),
            );
            $elem_comment = array(
                "ATTRIBUTES" => $elem_comment_attrs
            );
            $xmlArr["EASYFORM"]["DATAPANEL"]["ELEMENT"][] = $elem_comment;
        }
        parent::readMetaData($xmlArr);
    }

    protected function getChangeLogMessage($messageId)
    {
        $message = isset($this->changeLogMessages[$messageId]) ? $this->changeLogMessages[$messageId] : constant($messageId);
        $message = I18n::t($message, $messageId, "changelog");
        return @vsprintf($message, $params);
    }

    protected function _doUpdate($inputRecord, $currentRecord)
    {
        parent::_doUpdate($inputRecord, $currentRecord);

        $postFields = $_POST;
        $elem_mapping = array();
        foreach ($postFields as $elem_name => $value) {
            $elem = $this->dataPanel->get($elem_name);
            $fld_name = $elem->fieldName;
            if ($elem) {
                $elem_mapping[$fld_name] = $elem;
            }
        }
        $logDO = $this->getDataObj()->getRefObject($this->logDO);
        if (!$logDO) {
            return true;
        }

        $cond_column = $logDO->association['CondColumn'];
        $cond_value = $logDO->association['CondValue'];

        if ($cond_column) {
            $type = $cond_value;
        }
        $foreign_id = $currentRecord['Id'];

        $logRecord = array();
        foreach ($inputRecord as $fldName => $fldVal) {
            $oldVal = $currentRecord[$fldName];
            if ($oldVal == $fldVal)
                continue;

            $elem = $elem_mapping[$fldName]->xmlMeta;
            if (!$elem) {
                $elem = $this->dataPanel->getByField($fldName)->xmlMeta;
            }
            if ($elem['ATTRIBUTES']['CLASS'] != 'Hidden') {
                $logRecord[$fldName] = array('old' => $oldVal, 'new' => $fldVal, 'element' => $elem);
            }
        }

        $comment = Openbizx::$app->getClientProxy()->getFormInputs("fld_changelog_comment");

        if (empty($logRecord) && empty($comment))
            return true;

        $formMetaLite = array(
            "name" => $this->objectName,
            "package" => $this->package,
            "message_file" => $this->messageFile,
        );

        // save to comment do
        $dataRec = new DataRecord(null, $logDO);
        $dataRec['foreign_id'] = $foreign_id;
        $dataRec['type'] = $type;
        $dataRec['form'] = serialize($formMetaLite);
        $dataRec['data'] = serialize($logRecord);
        $dataRec['comment'] = $comment;


        try {
            $dataRec->save();
        } catch (Openbizx\data\Exception $e) {
            $this->processDataException($e);
            return true;
        }

        $this->runEventLog();
        return true;
    }

}

