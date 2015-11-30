<?php
/**
 * Openbizx Cubi Application Platform
 *
 * LICENSE http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 *
 * @package   cubi.backup.form
 * @copyright Copyright (c) 2005-2011, Openbiz Technology LLC
 * @license   http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 * @link      http://code.google.com/p/openbiz-cubi/
 * @version   $Id: BackupForm.php 3351 2012-05-31 05:33:35Z rockyswen@gmail.com $
 */

use Openbizx\Openbizx;
use Openbizx\Core\Expression;
use Openbizx\Easy\EasyForm;

class BackupForm extends EasyForm
{
	protected $folder;
	protected $locationId;
	protected $locationName;
	
    protected function readMetadata(&$xmlArr)
    {
        parent::readMetaData($xmlArr);		        
        if(!$this->locationId){
        	$this->getLocationInfo(1);
        }
		$this->folder = OPENBIZ_APP_FILE_PATH.DIRECTORY_SEPARATOR."backup";
	}
	
	public function loadStatefullVars($sessionContext)
    {
        $sessionContext->loadObjVar("backup.form.BackupForms", "LocationId", $this->locationId);
        $sessionContext->loadObjVar("backup.form.BackupForms", "LocationName", $this->locationName);
        $sessionContext->loadObjVar("backup.form.BackupForms", "Folder", $this->folder);
        return parent::loadStatefullVars($sessionContext);
    }
 
    public function saveStatefullVars($sessionContext)
    {
        $sessionContext->saveObjVar("backup.form.BackupForms", "LocationId", $this->locationId);
        $sessionContext->saveObjVar("backup.form.BackupForms", "LocationName", $this->locationName);
        $sessionContext->saveObjVar("backup.form.BackupForms", "Folder", $this->folder);
        return parent::saveStatefullVars($sessionContext);
    }
    
    public function getLocationInfo($id)
    {
    	$locationRec = Openbizx::getObject("backup.do.BackupDeviceDO")->fetchById($id);
    	if($locationRec){
	    	$this->locationId = $locationRec["Id"];
    		$this->locationName =  $locationRec["name"];            
	        $this->folder = Expression::evaluateExpression($locationRec['location'],null);            
	        $this->folder = Expression::evaluateExpression($locationRec['location'],null);
    	}	            
    }
	
	public function runSearch()
    {
        foreach ($this->searchPanel as $element)
        {
            if (!$element->fieldName)
                continue;

            $value = Openbizx::$app->getClientProxy()->getFormInputs($element->objectName);                                    
            $this->getLocationInfo($value);
        }

        $this->isRefreshData = true;

        $this->currentPage = 1;

        $this->runEventLog();
        $this->rerender();
    }    
    
	public function fetchData(){		
        if ($this->activeRecord != null)
            return $this->activeRecord;		
		
		preg_match("/\[Id\]='(.*?)'/si",$this->fixSearchRule,$match);
		$recId = $match[1];
		
		$resultRecords = $this->fetchFullDataSet();    
        foreach($resultRecords as $rec){
        	if($rec["Id"]==$recId){
        		$record = $rec;
        		break;
        	}
        }

        
        $this->recordId = $record['Id'];
        $this->setActiveRecord($record);        
        return $record;    
	}
	
	private function fetchFullDataSet(){
			//if the folder not exists then create it.
		if(!is_dir($this->folder)){
			$this->init_folder();
		}
	
		/*
		 * id - generated from filename
		 * filename
		 * filesize		 
		 * create_time
		 * update_time
		 */
		$resultRecords = array();
		try{
		foreach(glob($this->folder.DIRECTORY_SEPARATOR."*.tar.gz") as $filename){
			$record = array(
			"Id"		=> md5($filename),
			"type"		=> "tarball",
			"file"		=> $filename,
			"filename" 	=> basename($filename),
			"filesize" 	=> $this->format_bytes(filesize($filename)),
			"update_time" => date("Y-m-d H:i:s",filemtime($filename)),
			"timestamp" => filemtime($filename),
			);
			$resultRecords[filemtime($filename)]=$record;
		}		
		foreach(glob($this->folder.DIRECTORY_SEPARATOR."*.sql") as $filename){
			$record = array(
			"Id"		=> md5($filename),
			"type"		=> "sql",
			"file"		=> $filename,
			"filename" 	=> basename($filename),
			"filesize" 	=> $this->format_bytes(filesize($filename)),
			"update_time" => date("Y-m-d H:i:s",filemtime($filename)),
			"timestamp" => filemtime($filename),
			);
			$resultRecords[filemtime($filename)]=$record;			
		}	
		}catch(Exception $e)
		{
			
		}
		krsort($resultRecords);
		return $resultRecords;
	}
	
	public function fetchDataSet(){

		$resultRecords = $this->fetchFullDataSet();
		//paging		
		if(is_array($resultRecords)){
			$result = array_slice($resultRecords,($this->currentPage-1)*$this->range,$this->range);
		}			
		$this->totalRecords = count($resultRecords);
        if ($this->range && $this->range > 0)
            $this->totalPages = ceil($this->totalRecords/$this->range);
		return $result;
		
	}	


	
	public function deleteRecord($id=null){
		if ($id==null || $id=='')
            $id = Openbizx::$app->getClientProxy()->getFormInputs('_selectedId');

        $selIds = Openbizx::$app->getClientProxy()->getFormInputs('row_selections', false);
        if ($selIds == null)
            $selIds[] = $id;

       $resultRecords = $this->fetchFullDataSet();
            
        foreach ($selIds as $id)
        {
            foreach($resultRecords as $rec){
	        	if($rec["Id"]==$id){	        		
	        		@unlink($rec["file"]);
	        		break;
	        	}
	        }                	        	
        }
        
        if (strtoupper($this->formType) == "LIST")
            $this->rerender();

        $this->runEventLog();
        $this->processPostAction();        
	}		
	
	public function Download($id=null){
		if(!$id){
			$id = Openbizx::$app->getClientProxy()->getFormInputs('_selectedId');

			$selIds = Openbizx::$app->getClientProxy()->getFormInputs('row_selections', false);
			if($id==null){
				$id=$selIds[0];
			}
			if(!$id){
				$id=$this->recordId;
			}
			if(!$id){
				return;
			}
		}
		$resultRecords = $this->fetchDataSet(); 
        foreach($resultRecords as $rec){
	        if($rec["Id"]==$id){
	        	$record = $rec;	        	
	        	break;
	        }
        }        
        
        $filename = $record["file"];        		
		if(is_file($filename)){
			header("Content-Length: ".filesize($filename));
			header('Content-Disposition: attachment; filename="'.basename($filename).'"');
        	readfile($filename);
		}else{
		}
        return;
	}
	
	public function Backup(){
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

        //create backup file
        $filename = $recArr['filename'];
        $filename = str_replace(" ","_",$filename);
        
        $filename.="_".str_replace(" ","_",$recArr['database']);
        
        if($recArr['timestamp']){
        	$filename.="_".$recArr['timestamp'];
        }
        switch($recArr['mode'])
        {
        	case 'db_only':
        		$result = $this->_dumpDatabase($filename, $recArr['database'],$recArr['drop_table']);
        		break;
        		
        	case 'db_files':
        		$dbfile = $this->_dumpDatabase($filename,$recArr['database'], 1);        		
        		$result = $this->_dumpUserFiles($filename, $dbfile);
        		break;

        	case 'all_files':
        		$dbfile = $this->_dumpDatabase($filename,$recArr['database'], 1);        		
        		$result = $this->_dumpAllFiles($filename, $dbfile);
        		break; 
        }
        
        
        
    
        $this->recordId = md5($result);
        

        // in case of popup form, close it, then rerender the parent form
        if ($this->parentFormName)
        {
            $this->close();

            $this->renderParent();
        }

        $this->processPostAction();		
	}
	
	private function _dumpDatabase($filename,$dbname,$droptable)
	{
		$filename.=".sql";
        $filename = $this->folder.DIRECTORY_SEPARATOR.$filename;
        
		$dbconfigList = BizSystem::getConfiguration()->getDatabaseInfo();
        $dbconfig = $dbconfigList[$dbname];
                      
        
        if(strtolower($dbconfig["Driver"])!='pdo_mysql'){
        	return;
        }
        
        include_once dirname(dirname(__FILE__))."/lib/MySQLDump.class.php";
        $backup = new MySQLDump(); 
        
        if($droptable==1){
        	$backup->droptableifexists = true; 
        }else{
        	$backup->droptableifexists = false;
        }
        if($dbconfig["Port"]){
        	$dbHost = $dbconfig["Server"].":".$dbconfig["Port"];
        }else{
        	$dbHost = $dbconfig["Port"];
        }
        $dbc=$backup->connect($dbHost,$dbconfig["User"],$dbconfig["Password"],$dbconfig["DBName"],$dbconfig["Charset"]);
        if(!$dbc){
        	echo $backup->mysql_error;
        }                 
        $backup->dump();  
        $data = $backup->output;
        file_put_contents($filename,$data);		
        return $filename;
	}
	
	private function _dumpUserFiles($filename,$db_backup){
		$filename.=".tar.gz";
        $filename = $this->folder.DIRECTORY_SEPARATOR.$filename;
        $db_tmpfile = OPENBIZ_APP_PATH.DIRECTORY_SEPARATOR."database.sql";   
        copy($db_backup,$db_tmpfile);
		$cmd = "tar czf $filename -C '".OPENBIZ_APP_PATH."' --exclude '.svn' --exclude 'files/cache' --exclude 'files/backup' ./files ./database.sql";
		@exec($cmd,$output);
		@unlink($db_tmpfile);
		@unlink($db_backup);
		return $filename;
	}
	
	private function _dumpAllFiles($filename,$db_backup){
		$filename.=".tar.gz";
        $filename = $this->folder.DIRECTORY_SEPARATOR.$filename;
        $db_tmpfile = OPENBIZ_APP_PATH.DIRECTORY_SEPARATOR."database.sql";        
        copy($db_backup,$db_tmpfile);
		$cmd = "tar czf $filename -C '".OPENBIZ_APP_PATH."' --exclude '.svn' --exclude './log' --exclude './session' --exclude 'template/cpl' --exclude 'files/cache' --exclude 'files/backup' ./";
		@exec($cmd,$output);
		@unlink($db_tmpfile);
		@unlink($db_backup);
		return $filename;
	}	
	
	public function Upload(){
        $recArr = $this->readInputRecord();
        $this->setActiveRecord($recArr);	
		if(!$recArr['filename']){
			$this->errors = array("fld_name"=>$this->getMessage("FILE_TYPE_INCORRECT"));
			$this->updateForm();
			return;
		}
		
      	$filename = $this->folder.DIRECTORY_SEPARATOR.basename($recArr['filename']);
      	if(preg_match("/.sql$/si",$recArr['filename'])){
      		$recArr['mode']='db';
      	}
		if($recArr["import"]==1){
			switch($recArr['mode'])
	        {
	        	case 'db':	  
	        		$this->_RestoreDB($recArr["database"],$filename,$recArr["charset"]);
	        		break;

	        	case 'db_only':
	        		$db_tmpfile = $this->_RestoreDBFile($filename);
	        		$this->_RestoreDB($recArr["database"],$db_tmpfile,$recArr["charset"]);
	        		@unlink($db_tmpfile);
	        		break;
	        		
	        	case 'files_only':
	        		 $this->_RestoreUserFiles($filename);
	        		break; 
	        		
	        	case 'db_files':
	        		$db_tmpfile = $this->_RestoreDBFile($filename);
	        		$this->_RestoreDB($recArr["database"],$db_tmpfile,$recArr["charset"]);
	        		@unlink($db_tmpfile);
	        		$this->_RestoreUserFiles($filename);
	        		break;		
	        }			
		}
		

        $this->recordId = md5($filename);
        if ($this->parentFormName)
        {
            $this->close();

            $this->renderParent();
        }

        $this->processPostAction();	        
	}
	
	public function Restore($id=null){
		
		if(!$id){
			$id = Openbizx::$app->getClientProxy()->getFormInputs('_selectedId');
			if(!$id){
				return;
			}
		}		
		$this->recordId = $id;
		$recArr = $this->readInputRecord();
		
		$this->fixSearchRule="[Id]='$id'";
		$recArrFile = $this->fetchData();
		
		if(!$recArr['import']){
			$this->errors = array("fld_import"=>$this->getMessage("PLEASE_CHECK_AGREEMENT"));
			$this->rerender();
			return;
		}
	    switch($recArr['mode'])
        {
        	case 'db':
        		$this->_RestoreDB($recArr["database"],$recArrFile['file'],$recArr["charset"]);
        		break;
        		        	
        	case 'db_only':
        		$db_tmpfile = $this->_RestoreDBFile($recArrFile['file']);
        		$this->_RestoreDB($recArr["database"],$db_tmpfile,$recArr["charset"]);
        		@unlink($db_tmpfile);
        		break;
        		
        	case 'files_only':
        		 $this->_RestoreUserFiles($recArrFile['file']);
        		break; 
        		
        	case 'db_files':
        		$db_tmpfile = $this->_RestoreDBFile($recArrFile['file']);
        		$this->_RestoreDB($recArr["database"],$db_tmpfile,$recArr["charset"]);
        		@unlink($db_tmpfile);
        		$this->_RestoreUserFiles($recArrFile['file']);
        		break;


        }		
		
		$this->notices = array($this->getMessage("RESTORE_SUCCESSFUL"));
		$this->rerender();
		
		
	}
	
	
	
	public function gotoRestore(){
		$this->recordId = Openbizx::$app->getClientProxy()->getFormInputs('_selectedId');
		$this->switchForm("backup.form.BackupRestoreForm",$this->recordId);
	}
	
	private function _restoreDB($db,$sqlfile,$charset=null){
		$query = trim(file_get_contents($sqlfile));
		if (empty($query))
        	return true;
        	
		$db = Openbizx::$app->getDbConnection($db);
		if($charset){
			$db->exec("SET NAMES '$charset';");
		}

        include_once Openbizx::$app->getModulePath()."/system/lib/MySQLDumpParser.php";
	    $queryArr = MySQLDumpParser::parse($query);
        foreach($queryArr as $query){
			try {
		    	$db->exec($query);
		    } catch (Exception $e) {
		        return false;
		   	}
	    }
	    return true;
	}
	
	private function _restoreDBFile($filename)
	{
		if(!is_dir(CUBI_TEMPFILE_PATH)){
			@mkdir(CUBI_TEMPFILE_PATH);
			@chmod(CUBI_TEMPFILE_PATH,0777);
		}
		$db_tmpfile = CUBI_TEMPFILE_PATH.DIRECTORY_SEPARATOR."database.sql";
		$cmd = "tar xzf $filename -C '".CUBI_TEMPFILE_PATH."' ./database.sql";
		@exec($cmd,$output);
		if(is_file($db_tmpfile))
		{
			return $db_tmpfile;
		}
	}
	
	private function _RestoreUserFiles($filename)
	{
		$cmd = "tar xzf $filename -C '".OPENBIZ_APP_PATH."' --exclude './database.sql'";
		@exec($cmd,$output);
	}
	
	
	
	private function format_bytes($size) {
	    $units = array(' B', ' KB', ' MB', ' GB', ' TB');
	    for ($i = 0; $size >= 1024 && $i < 4; $i++) $size /= 1024;
	    return round($size, 2).$units[$i];
	}	
	
	private function init_folder(){
		@mkdir($this->folder,0777,true);
		$this->init_htaccess_protect();
		return;
	}
	
	private function init_htaccess_protect(){
		$filename = $this->folder.DIRECTORY_SEPARATOR.".htaccess";
		$data = "Deny from all";
		return file_put_contents($filename,$data);		
	}
}
