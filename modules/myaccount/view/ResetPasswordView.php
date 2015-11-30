<?php

use Openbizx\Easy\WebPage;

class ResetPasswordView extends WebPage {

    protected $forceResetPassword = false;

    public function loadStatefullVars($sessionContext) {
        $sessionContext->loadObjVar($this->objectName, "ForceResetPassword", $this->forceResetPassword);
    }

    public function saveStatefullVars($sessionContext) {
        $sessionContext->saveObjVar($this->objectName, "ForceResetPassword", $this->forceResetPassword);
    }

    public function isForceResetPassword() {
        return $this->forceResetPassword;
    }

    public function render() {
        if (isset($_GET['force'])) {
            $this->forceResetPassword = true;
        } else {
            $this->forceResetPassword = false;
        }
        return parent::render();
    }

}
