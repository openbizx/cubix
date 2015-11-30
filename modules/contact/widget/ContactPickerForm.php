<?php
/**
 * Openbizx Cubi Application Platform
 *
 * LICENSE http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 *
 * @package   cubi.contact.widget
 * @copyright Copyright (c) 2005-2011, Openbiz Technology LLC
 * @license   http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 * @link      http://code.google.com/p/openbiz-cubi/
 * @version   $Id: ContactPickerForm.php 3356 2012-05-31 05:47:51Z rockyswen@gmail.com $
 */

use Openbizx\Openbizx;
use Openbizx\Easy\PickerForm;

class ContactPickerForm extends PickerForm
{
	public function insertToParent()
    {        
		$recArr = $this->readInputRecord();
        $this->setActiveRecord($recArr);
        if (count($recArr) == 0)
            return;
		
        //generate fast_index
        $svcobj=  Openbizx::getService("service.chineseService");
        if($svcobj->isChinese($recArr['first_name'])){
        	$fast_index = $svcobj->Chinese2Pinyin($recArr['first_name']);
        }else{
        	$fast_index = $recArr['first_name'];
        }
        $recArr['fast_index'] = substr($fast_index,0,1); 
        
        if(!$recArr['company']){
        	$recArr['company']='N/A';
        }
        
        try
        {
            $this->ValidateForm();
        }
        catch (Openbizx\Validation\Exception $e)
        {        	
            $this->processFormObjError($e->errors);
            return;
        }

        $recId = $this->_doInsert($recArr);        
        
        $selIds[] = $recId;
        
        // if no parent elem or picker map, call AddToParent
        if (!$this->parentFormElemName)
        {
            $this->addToParent($selIds);
        }                

        // if has parent elem and picker map, call JoinToParent
        if ($this->parentFormElemName && $this->pickerMap)
        {
            $this->joinToParent($selIds);
        }
        
    }    
}
