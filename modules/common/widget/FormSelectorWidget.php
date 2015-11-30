<?php

/**
 * Openbizx Cubi Application Platform
 *
 * LICENSE http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 *
 * @package   cubi.common.widget
 * @copyright Copyright (c) 2005-2011, Openbiz Technology LLC
 * @license   http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 * @link      http://code.google.com/p/openbiz-cubi/
 * @version   $Id: FormSelectorWidget.php 3355 2012-05-31 05:43:33Z rockyswen@gmail.com $
 */
use Openbizx\Openbizx;
use Openbizx\Easy\EasyForm;

class FormSelectorWidget extends EasyForm
{

    public $viewMode;
    public $lastViewMode;
    private $_DefaultViewMode;

    protected function readMetadata(&$xmlArr)
    {
        parent::readMetaData($xmlArr);
        $this->_DefaultViewMode = isset($xmlArr["EASYFORM"]["ATTRIBUTES"]["DEFAULTSELECTEDFORM"]) ? $xmlArr["EASYFORM"]["ATTRIBUTES"]["DEFAULTSELECTEDFORM"] : null;
    }

    public function getViewMode()
    {
        if ($this->viewMode) {
            $data = $this->viewMode;
        } else {
            $data = $this->_DefaultViewMode;
        }
        return $data;
    }

    public function fetchData()
    {
        $data = array();
        if ($this->viewMode) {
            $data['viewmode'] = $this->viewMode;
        } else {
            $data['viewmode'] = $this->_DefaultViewMode;
        }
        return $data;
    }

    public function switchViewMode()
    {
        if (!$this->lastViewMode) {
            $this->lastViewMode = $this->getViewMode();
        }
        $viewObj = $this->getWebpageObject();
        //$viewObj = $this->getView();
        if ($viewObj->lastRenderedForm &&
                $viewObj->lastRenderedForm != 'help.form.HelpWidgetListForm' &&
                $viewObj->lastRenderedForm != 'notification.widget.NotificationWidgetForm'
        ) {
            $this->lastViewMode = $viewObj->lastRenderedForm;
        }
        $recArr = $this->readInputRecord();
        $this->viewMode = $recArr['viewmode'];
        $targetForm = $recArr['viewmode'];
        $formObj = Openbizx::getObject($targetForm);
        $formHTML = $formObj->render();
        Openbizx::$app->getClientProxy()->redrawForm($this->lastViewMode, $formHTML);
        $this->lastViewMode = $this->viewMode;
    }

    public function loadStatefullVars($sessionContext)
    {
        $sessionContext->loadObjVar($this->objectName, "ViewMode", $this->viewMode);
        parent::loadStatefullVars($sessionContext);
    }

    public function saveStatefullVars($sessionContext)
    {
        $sessionContext->saveObjVar($this->objectName, "ViewMode", $this->viewMode);
        parent::saveStatefullVars($sessionContext);
    }

}

