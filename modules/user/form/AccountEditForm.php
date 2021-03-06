<?php
/**
 * Openbizx Cubi Application Platform
 *
 * LICENSE http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 *
 * @package   cubi.user.form
 * @copyright Copyright (c) 2005-2011, Openbiz Technology LLC
 * @license   http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 * @link      http://code.google.com/p/openbiz-cubi/
 * @version   $Id: AccountEditForm.php 3375 2012-05-31 06:23:11Z rockyswen@gmail.com $
 */

use Openbizx\Openbizx;

include_once Openbizx::$app->getModulePath()."/system/form/UserForm.php";

/**
 * AccountEditForm class - implement the logic of edit my account form
 *
 * @package user.form
 * @author Rocky Swen
 * @copyright Copyright (c) 2005-2009
 * @access public
 */
class AccountEditForm extends UserForm
{
    protected $_userId = null;
    
    function __construct(&$xmlArr)
    {
        parent::__construct($xmlArr);
        
        // read user profile and set fix search rule
         $profile = Openbizx::$app->getUserProfile();
        if ($profile && $profile['Id'])
            $this->_userId = $profile['Id'];
    }
    
    public function render()
    {
        // set fix search rule
        if (!$this->_userId)
            return Openbizx::$app->getClientProxy()->redirectView(OPENBIZ_ACCESS_DENIED_VIEW);
        $this->fixSearchRule = "[Id]=".$this->_userId;
        return parent::render();
    }
    
    public function rerender()
    {
        // clean active record to force query again
        $this->activeRecord = null;
        // set fix search rule
        if (!$this->_userId)
            return Openbizx::$app->getClientProxy()->redirectView(OPENBIZ_ACCESS_DENIED_VIEW);
        $this->fixSearchRule = "[Id]=".$this->_userId;
        return parent::rerender();
    }
    
    /**
     * Update account with user inputs
     *
     * @return void
     */
    public function UpdateAccount()
    {
        $currentRec = $this->fetchData();
        $recArr = $this->readInputRecord();
        $this->setActiveRecord($recArr);
        
        try
        {
            $this->ValidateForm();
        }
        catch (Openbizx\Validation\Exception $e)
        {
            $this->processFormObjError($e->errors);
            return;
        }

        if (count($recArr) == 0)
            return;

        $this->_doUpdate($recArr, $currentRec);
        
        // if 'notify email' option is checked, send confirmation email to user email address
        // ...
        
        $this->notices[] = $this->GetMessage("USER_DATA_UPDATED");

       	//run eventlog        
        $eventlog 	= Openbizx::getService(OPENBIZ_EVENTLOG_SERVICE);        
    	$eventlog->log("USER_MANAGEMENT", "MSG_USER_RESET_PASSWORD");        
        
        $this->rerender();
    }
   
}  
