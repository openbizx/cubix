<?php
/**
 * Openbizx Cubi Application Platform
 *
 * LICENSE http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 *
 * @package   cubi.repository.release.widget
 * @copyright Copyright (c) 2005-2011, Openbiz Technology LLC
 * @license   http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 * @link      http://code.google.com/p/openbiz-cubi/
 * @version   $Id: ReleaseForm.php 3369 2012-05-31 06:13:56Z rockyswen@gmail.com $
 */

use Openbizx\Openbizx;
use Openbizx\Easy\PickerForm;

class ReleaseForm extends PickerForm
{
	
	public $basePath = 'attachment';
	
	public function uploadFile()
	{
		if (empty($_FILES)) return;
		
		$upload_user_dir = Openbizx::$app->getUserProfile("Id");						
		$upload_user_dir = (int)$upload_user_dir;
		$upload_dir = "app_release";
		
		try {

                                
            if(!file_exists(OPENBIZ_PUBLIC_UPLOAD_PATH.DIRECTORY_SEPARATOR.$this->basePath.DIRECTORY_SEPARATOR.$upload_dir.DIRECTORY_SEPARATOR.$upload_user_dir)) {
                @mkdir(OPENBIZ_PUBLIC_UPLOAD_PATH.DIRECTORY_SEPARATOR.$this->basePath.DIRECTORY_SEPARATOR.$upload_dir.DIRECTORY_SEPARATOR.$upload_user_dir,0777,true);
            }				
            
            $targetPath = OPENBIZ_PUBLIC_UPLOAD_PATH.DIRECTORY_SEPARATOR.$this->basePath.DIRECTORY_SEPARATOR.$upload_dir.DIRECTORY_SEPARATOR.$upload_user_dir.DIRECTORY_SEPARATOR;
            
            $targetURL = OPENBIZ_PUBLIC_UPLOAD_URL."/".$this->basePath."/".$upload_dir."/".$upload_user_dir."/";
            
            $tempFile = $_FILES['Filedata']['tmp_name'];	
            $newFilename = 	date("YmdHis")."_".uniqid().'.att';
            $targetFile =  str_replace('//',DIRECTORY_SEPARATOR,$targetPath) . $newFilename;
            
            move_uploaded_file($tempFile,$targetFile);
            
		} catch(Exception $e){
			file_put_contents(OPENBIZ_APP_PATH.'\out.txt', $e->getMessage());			
		}
		$output =array();
		$output['file_path'] = $targetPath.$newFilename;
		$output['file_url'] = $targetURL.$newFilename;
		$output['file_name'] = $_FILES['Filedata']['name'];

		echo base64_encode(json_encode($output));
        exit;
	}
	
	public function checkFile()
	{
		$fileArray = array();
		foreach ($_POST as $key => $value) {
			if ($key != 'folder') {
				if (file_exists($_SERVER['DOCUMENT_ROOT'] . $_POST['folder'] . '/' . $value)) {
					$fileArray[$key] = $value;
				}
			}
		}
		echo json_encode($fileArray);	
		exit;
	}
	
	public function fileUploadComplete($fileObjStr){	
		
		$fileObj = json_decode(base64_decode($fileObjStr),true);
		
		$recArr = $this->readInputRecord();
	    $this->setActiveRecord($recArr);
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

        //add file attributes
        $recArr['filesize'] =  filesize($fileObj['file_path']);
        $recArr['md5'] = md5_file($fileObj['file_path']);
        $recArr['sha256'] = sha1_file($fileObj['file_path']);
        $recArr['filename'] = $fileObj['file_name'];
        $recArr['path'] = $fileObj['file_path'];
        $recArr['url'] = $fileObj['file_url'];
        $recArr['download_count'] = 0;
        
		if (!$this->parentFormElemName)
        {
        	//its only supports 1-m assoc now	        	        
	        $parentForm = Openbizx::getObject($this->parentFormName);
        	//$parentForm->getDataObj()->clearSearchRule();
	        $parentDo = $parentForm->getDataObj();
	        
	        $column = $parentDo->association['Column'];
	    	$field = $parentDo->getFieldNameByColumn($column);	    	    	
	    	$parentRefVal = $parentDo->association["FieldRefVal"];
	    	
			$recArr[$field] = $parentRefVal;
	    	$cond_column = $parentDo->association['CondColumn'];
	    	$cond_value = $parentDo->association['CondValue'];
	    	if($cond_column)
	    	{
	    		$cond_field = $parentDo->getFieldNameByColumn($cond_column);
	    		$recArr[$cond_field] = $cond_value;
	    	}    	
        }                

        if ($this->parentFormElemName && $this->pickerMap)
        {
            return ; //not supported yet
        }
        $recId = $parentDo->InsertRecord($recArr);
            
        $selIds[] = $recId;

		exit;
	}
	
	public function allUploadComplete(){
		$this->close();	
		$parentForm = Openbizx::getObject($this->parentFormName);
		usleep(1000000);
		$parentForm->rerender();
	}
	
    public function loadDialog($formName, $id=null)
    {
    	$paramFields = array();
        if ($id==null && $this->recordId!=null)
        {
        	$id = $this->recordId;
        }
        if($id!=null)
            $paramFields["Id"] = $id;
        $this->_showForm($formName, "Dialog", $paramFields);
    }	
	
	public function DeleteRecord($id=null){		
        if ($id==null || $id=='')
            $id = Openbizx::$app->getClientProxy()->getFormInputs('_selectedId');

        $selIds = Openbizx::$app->getClientProxy()->getFormInputs('row_selections', false);
        if ($selIds == null)
            $selIds[] = $id;
        foreach ($selIds as $id)
        {        	
            $dataRec = $this->getDataObj()->fetchById($id);
            if(!$dataRec){
            	continue;
            }
            //remove file 
            $file = $dataRec['path'];
            @unlink($file);
            
            if(!$this->canDeleteRecord($dataRec))
            {
            	$this->errorMessage = $this->getMessage("FORM_OPEATION_NOT_PERMITTED",$this->objectName);         
        		if (strtoupper($this->formType) == "LIST"){
        			Openbizx::$app->getLog()->log(LOG_ERR, "DATAOBJ", "DataObj error = ".$errorMsg);
        			Openbizx::$app->getClientProxy()->showClientAlert($this->errorMessage);
        		}else{
        			$this->processFormObjError(array($this->errorMessage));	
        		}	
        		return;
            }
            
            // take care of exception
            try
            {
                $dataRec->delete();
            } catch (Openbizx\data\Exception $e)
            {
                // call $this->processDataException($e);
                $this->processDataException($e);
                return;
            }
        }
        if (strtoupper($this->formType) == "LIST")
            $this->rerender();

        $this->runEventLog();
        $this->processPostAction();		
	}
	
	public function close(){
		$parentForm = Openbizx::getObject($this->parentFormName);
		$parentForm->rerender();
		return parent::close();
	}
	
	public function FileDownload($id=null){
		include_once (Openbizx::$app->getModulePath().'/attachment/lib/class.httpdownload.php');
		if ($id==null || $id=='')
            $id = Openbizx::$app->getClientProxy()->getFormInputs('_selectedId');
        if(!$id)
        	$id=$this->recordId;
		$dataRec = $this->getDataObj()->fetchById($id);
		$file_source = $dataRec['path'];
		$file_name = $dataRec['filename'];
		
					
		
     	$object = new httpdownload();
     	$object->filename=$file_name;
    	$object->set_byfile($file_source); //Download from a file
    	$object->mime = $type;
    	$object->use_resume = true; //Enable Resume Mode
    	$object->download(); //Download File
    	exit;
	}

}
