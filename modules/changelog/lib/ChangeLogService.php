<?php
/**
 * Openbizx Cubi Application Platform
 *
 * LICENSE http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 *
 * @package   cubi.changelog.lib
 * @copyright Copyright (c) 2005-2011, Openbiz Technology LLC
 * @license   http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 * @link      http://code.google.com/p/openbiz-cubi/
 * @version   $Id: ChangeLogService.php 3872 2012-08-09 11:30:28Z hellojixian@gmail.com $
 */

use Openbizx\Data\DataRecord;

class ChangeLogService
{
	protected  $logDO = "changelog.do.ChangeLogDO";
	
	public function LogDataChanges( $formObj,
									$inputRecord,
									$currentRecord,
									$comment=null,
									$panel=null )
	{
		if(!$panel)
		{
			$panel = clone $formObj->dataPanel;
		}		
		$postFields = $_POST;
   		$elem_mapping = array();
   		
   		foreach($postFields as $elem_name=>$value)
   		{
   			$elem = $panel->get($elem_name);
   			$fld_name = $elem->fieldName;
   			if($elem){
   				$elem_mapping[$fld_name] = $elem;
   			}
   		}
   		$logDO = $formObj->getDataObj()->getRefObject($this->logDO);
		if (!$logDO) {
			return true;
		}
				
    	$cond_column = $logDO->association['CondColumn'];
    	$cond_value = $logDO->association['CondValue'];
    	
    	if($cond_column)
    	{
    		$type = $cond_value;
    	}
		$foreign_id = $currentRecord['Id'];
    	
		$logRecord = array();
   		foreach ($inputRecord as $fldName=>$fldVal)
		{
			$oldVal = $currentRecord[$fldName];
			if ($oldVal == $fldVal)
				continue;
			
			$elem = $elem_mapping[$fldName]->xmlMeta;		
			if(!$elem){
				$elem = $panel->getByField($fldName)->xmlMeta;
			}	
			$logRecord[$fldName] = array('old'=>$oldVal, 'new'=>$fldVal, 'element'=>$elem);
		}
		
		if (empty($logRecord))
			return true;
			
		$formMetaLite = array(
			"name" 		=> $formObj->objectName,
			"package" 	=> $formObj->package,
			"message_file" 	=> $formObj->messageFile,		
		);
		
   		// save to comment do
		$dataRec = new DataRecord(null, $logDO); 
		$dataRec['foreign_id'] = $foreign_id;
		$dataRec['type'] = $type;
		$dataRec['form'] = serialize( $formMetaLite );
		$dataRec['data'] = serialize( $logRecord );
		$dataRec['comment'] = $comment;
		try {
			$dataRec->save();
		}
        catch (Openbizx\data\Exception $e)
        {
            $this->processDataException($e);
            return true;
        }	
        return true;	
	}
}
