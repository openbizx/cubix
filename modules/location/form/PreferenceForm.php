<?php
/**
 * Openbizx Cubi Application Platform
 *
 * LICENSE http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 *
 * @package   cubi.location.form
 * @copyright Copyright (c) 2005-2011, Openbiz Technology LLC
 * @license   http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 * @link      http://code.google.com/p/openbiz-cubi/
 * @version   $Id: PreferenceForm.php 3362 2012-05-31 06:03:29Z rockyswen@gmail.com $
 */

/**
 * Openbizx Cubi 
 *
 * LICENSE
 *
 * This source file is subject to the BSD license that is bundled
 * with this package in the file LICENSE.txt.
 *
 * @package   user.form
 * @copyright Copyright (c) 2005-2011, Rocky Swen & Jixian Wang 
 * @license   http://www.opensource.org/licenses/bsd-license.php
 * @link      http://www.phpopenbiz.org/
 * @version   $Id: PreferenceForm.php 3362 2012-05-31 06:03:29Z rockyswen@gmail.com $
 */

use Openbizx\Easy\EasyForm;

/**
 * PreferenceForm class 
 *
 * @package user.form
 * @author Jixian Wang
 * @copyright Copyright (c) 2005-2011
 * @access public
 */
class PreferenceForm extends EasyForm
{    
    
    public function fetchData(){
        $prefRecord = array();
        return $prefRecord;    
    }
    
    public function updateRecord()
    {
        $recArr = $this->readInputRecord();

        if (count($recArr) == 0)
            return;

        try
        {
            $this->ValidateForm();
        }
        catch (Openbizx\Validation\Exception $e)
        {
            $this->processFormObjError($e->errors);
            return;
        }
		
        
        foreach ($this->dataPanel as $element)
        {
            $value = $recArr[$element->fieldName];
            if ($value === null){ 
            	continue;
            } 
            
            if(substr($element->fieldName,0,1)=='_'){
	           	$name = substr($element->fieldName,1);
	            //update default app_init setting
	            $config_file = OPENBIZ_APP_PATH.'/bin/app_init.php';
	            switch($name){
	            	case "latitude":	            		
	            		if($value!=CUBI_DEFAULT_LATITUDE){
	            			
	            			$data = file_get_contents($config_file);	            			
	            			$data = preg_replace("/define\([\'\\\"]{1}CUBI_DEFAULT_LATITUDE[\'\\\"]{1}.*?\)\;/i","define('CUBI_DEFAULT_LATITUDE','$value');",$data);	            			
	            			@file_put_contents($config_file,$data);
	            		}
	            		break;
					case "longtitude":
						if($value!=CUBI_DEFAULT_LONGTITUDE){
            				$data = file_get_contents($config_file);	            			
            				$data = preg_replace("/define\([\'\\\"]{1}CUBI_DEFAULT_LONGTITUDE[\'\\\"]{1}.*?\)\;/i","define('CUBI_DEFAULT_LONGTITUDE','$value');",$data);	            			
            				@file_put_contents($config_file,$data);
						}
	            		break;	         		            			            		        		
	            }
            }
        }
       	
	            		            	
        // in case of popup form, close it, then rerender the parent form
        if ($this->parentFormName)
        {
            $this->close();
            $this->renderParent();
        }

        $this->processPostAction();

    }

}  
