<?php
/**
 * Openbizx Cubi Application Platform
 *
 * LICENSE http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 *
 * @package   cubi.market.repository.form
 * @copyright Copyright (c) 2005-2011, Openbiz Technology LLC
 * @license   http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 * @link      http://code.google.com/p/openbiz-cubi/
 * @version   $Id: RepositoryForm.php 5078 2013-01-07 09:47:00Z hellojixian@gmail.com $
 */

use Openbizx\Openbizx;
use Openbizx\Easy\EasyForm;

class RepositoryForm extends EasyForm
{
	public $repoValidated = 'N';
	protected $repoInfo = array();
	
	public function checkRepo()
	{
		$rec= $this->readInputRecord();
		$repo_uri = $rec['repository_uri'];
		$svc = Openbizx::getService("market.lib.PackageService");
		$repoInfo = $svc->discoverRepository($repo_uri);
		if(!count($repoInfo))
		{
			$this->errors = array(
        		"fld_uri"=> $this->getMessage("REPO_INVALID")
        	);
        	$recArr = $this->readInputRecord();
       		$this->setActiveRecord($recArr);
        	$this->processFormObjError($this->errors);
           	return false;
		}
		$this->repoInfo = $repoInfo;
		$this->repoValidated = 'Y';
		$this->updateForm();
	}
	
	public function fetchData()
	{
		$result = parent::fetchData();
		
		if(count($this->repoInfo)==0 && $result['repository_uri']!='' && $result['repository_uri']!='http://'){
			$repo_uri = $result['repository_uri'];
			$svc = Openbizx::getService("market.lib.PackageService");
			$this->repoInfo = $svc->discoverRepository($repo_uri);
		}
		
		if(is_array($this->repoInfo)){
			foreach($this->repoInfo as $key => $value)
			{
				$result[$key] = $value;
			}
		}
		return $result;
	}
	
	public function fetchDataSet()
	{
		$resultSet = parent::fetchDataSet();
		$svc = Openbizx::getService("market.lib.PackageService");
		foreach($resultSet as $key=>$record)
		{
			$repo_uri = $record['repository_uri'];
			$repoInfo = $svc->discoverRepository($repo_uri);
			$record['repository_name'] = $repoInfo['_repo_name'];
			$resultSet[$key] = $record;
		}
		return $resultSet;
	}
	
	public function insertRecord()
	{
		
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

		$repo_uri = $recArr['repository_uri'];
		$svc = Openbizx::getService("market.lib.PackageService");
		$repoInfo = $svc->discoverRepository($repo_uri);        
        $recArr['repository_uid'] = $repoInfo['_repo_uid'];
        $recArr['repository_name'] = $repoInfo['_repo_name'];
        $this->_doInsert($recArr);
        
        

        // in case of popup form, close it, then rerender the parent form
        if ($this->parentFormName)
        {
            $this->close();

            $this->renderParent();
        }

        $this->processPostAction();
	
	}
}
