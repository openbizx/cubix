<?php
/**
 * Openbizx Cubi Application Platform
 *
 * LICENSE http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 *
 * @package   cubi.market.application.form
 * @copyright Copyright (c) 2005-2011, Openbiz Technology LLC
 * @license   http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 * @link      http://code.google.com/p/openbiz-cubi/
 * @version   $Id: AppListForm.php 4679 2012-11-12 08:46:20Z hellojixian@gmail.com $
 */

use Openbizx\Openbizx;
use Openbizx\Easy\EasyForm;

class AppListForm extends EasyForm
{
	public $repoDO = "market.repository.do.RepositoryDO";
	protected $remoteSearchRule;
	protected $repoID;
	
	protected function getRepoInfo($uid)
	{
		$repoRec = Openbizx::getObject($this->repoDO,1)->fetchOne("[repository_uid]='$uid'");
		return $repoRec;
	}
	
	protected function getDefaultRepoURI()
	{		
		if($_POST['fld_repo_id'])
    	{
    		$repoRec = Openbizx::getObject("market.repository.do.RepositoryDO")->fetchById((int)$_POST['fld_repo_id']);
    	}
    	elseif($_GET['repo'])
    	{
    		$repoRec = Openbizx::getObject("market.repository.do.RepositoryDO")->fetchById((int)$_GET['repo']);
    	}
    	else
    	{
    		$repoRec = Openbizx::getObject("market.repository.do.RepositoryDO")->fetchOne("[status]=1");    	
    	}
    	$repo_id = $repoRec['Id'];
    	$repo_uri = $repoRec['repository_uri'];
    	if(!$repo_uri){
    		$repoRec = Openbizx::getObject("market.repository.do.RepositoryDO")->fetchOne("[status]=1");
    		$repo_uri = $repoRec['repository_uri'];
    	}
		if(substr($repo_uri,strlen($repo_uri)-1,1)!='/'){
        	$repo_uri .= '/';
        }	
        
        $this->repoID = $repo_id;
    	return $repo_uri;	
	}
	
	protected function fetchRepoList()
	{
		$rs = Openbizx::getObject($this->repoDO)->directFetch("[status]='1'");
		return $rs;
	}
	
	public function fetchDataSet()
	{
        if ($this->fixSearchRule)
        {
            if ($this->searchRule)
                $searchRule = $this->searchRule . " AND " . $this->fixSearchRule;
            else
                $searchRule = $this->fixSearchRule;
        }
        else
            $searchRule = $this->searchRule;        

        if($_POST['fld_cat_id']){
        	$cat_id = (int)$_POST['fld_cat_id'];
        	$catSearchRule = " [category_id]='$cat_id' ";
        	if($searchRule){
	       		$searchRule .=" AND $catSearchRule";
	       	}else{
	       		$searchRule = $catSearchRule;
	       	} 
        }
       	      	
       	$this->remoteSearchRule = $searchRule;
       	Openbizx::getService(ACL_SERVICE)->clearACLCache();
	}
	
	public function _fetchDataSet()
	{
		return parent::fetchDataSet();
	}
	
	public function sortRecord($sortCol, $order='ASC')
    {
        $element = $this->getElement($sortCol);
        // turn off the OnSort flag of the old onsort field
        $element->setSortFlag(null);
        // turn on the OnSort flag of the new onsort field
        if ($order == "ASC")
            $order = "DESC";
        else
            $order = "ASC";
        $element->setSortFlag($order);

        // change the sort rule and issue the query
        $this->sortRule="[" . $element->fieldName . "] $order";

        // move to 1st page
        $this->currentPage = 1;
        //$this->sortRule = "";

        $this->rerender();
    }	
}
