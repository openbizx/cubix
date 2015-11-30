<?php

use Openbizx\Openbizx;

class TutorialService
{

    const SESSION_VAR_NAME = "HELP_TUTORAIL_SHOWN";

    protected $tutorialDO = "help.tutorial.do.TutorialDO";
    protected $tutorialUserDO = "help.tutorial.do.TutorialUserDO";
    protected $tutorialForm = "help.tutorial.widget.TutorialForm";

    public function checkInstalledVersion()
    {
        $installedVersion = Openbizx::getService("system.lib.ModuleService")->isModuleInstalled("help");
        if (version_compare($installedVersion, "1.1") >= 0) {
            return true;
        } else {
            return false;
        }
    }

    public function autoShowTutorial($url, $formObj)
    {
        if (!$this->checkInstalledVersion()) {
            return false;
        }
        $tutorialId = $this->getTutorialId($url);
        if (!$tutorialId) {
            return false;
        }
        if ($this->_checkNeedShowTutorial($tutorialId)) {
            //show the form
            $formObj->loadDialog($this->tutorialForm, $tutorialId);
            //set it has been shown in session
            //$this->_setTutorialShownInSession($tutorialId);
        }
        return true;
    }

    public function getTutorialId($url)
    {
        if (!$this->checkInstalledVersion()) {
            return 0;
        }
        $tutorialRec = Openbizx::getObject($this->tutorialDO)->fetchOne("[url_match]='$url'");
        if (!$tutorialRec) {
            foreach (Openbizx::getObject($this->tutorialDO)->directfetch("[url_match] LIKE '%.*%'") as $record) {
                $match = $record['url_match'];
                $pattern = "@" . $match . "@si";
                if (preg_match($pattern, $url)) {
                    $tutorialRec = $record;
                    break;
                }
            }
        }
        $tutorialId = $tutorialRec['Id'];
        return (int) $tutorialId;
    }

    public function ShowTutorial($tutorialId, $formObj)
    {
        $formObj->loadDialog($this->tutorialForm, $tutorialId);
        return true;
    }

    protected function _checkNeedShowTutorial($tutorialId)
    {
        $tutorialShown = Openbizx::$app->getSessionContext()->getvar(self::SESSION_VAR_NAME);
        if ($tutorialShown[$tutorialId]) {
            return false;
        }
        $userId = Openbizx::$app->getUserProfile("Id");
        $showLog = Openbizx::getObject($this->tutorialUserDO)->fetchOne("[tutorial_id]='$tutorialId' AND [user_id]='$userId'");
        if (!$showLog) {
            return true;
        } else {
            if ($showLog['autoshow'] == 1) {
                return true;
            } else {
                return false;
            }
        }
    }

    protected function _setTutorialShownInSession($tutorialId)
    {
        $tutorialShown = Openbizx::$app->getSessionContext()->getvar(self::SESSION_VAR_NAME);
        $tutorialShown[$tutorialId] = true;
        Openbizx::$app->getSessionContext()->setVar(self::SESSION_VAR_NAME, $tutorialShown);
    }

    public function SetTutorialShown($tutorialId, $showOnNextLogin)
    {
        $this->_setTutorialShownInSession($tutorialId);
        $userId = Openbizx::$app->getUserProfile("Id");
        $logRec = Openbizx::getObject($this->tutorialUserDO)->fetchOne("[tutorial_id]='$tutorialId' AND [user_id]='$userId'");
        if (!$logRec) {
            $rec = array(
                "tutorial_id" => $tutorialId,
                "user_id" => $userId,
                "autoshow" => $showOnNextLogin
            );
            Openbizx::getObject($this->tutorialUserDO)->insertRecord($rec);
        } else {
            $logRec['autoshow'] = $showOnNextLogin;
            $logRec->save();
        }
        return true;
    }

}
