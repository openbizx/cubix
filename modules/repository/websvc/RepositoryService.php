<?php

/**
 * Openbizx Cubi Application Platform
 *
 * LICENSE http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 *
 * @package   cubi.repository.websvc
 * @copyright Copyright (c) 2005-2011, Openbiz Technology LLC
 * @license   http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 * @link      http://code.google.com/p/openbiz-cubi/
 * @version   $Id: RepositoryService.php 5076 2013-01-07 09:27:31Z hellojixian@gmail.com $
 */
use Openbizx\Openbizx;

include_once Openbizx::$app->getModulePath() . '/websvc/lib/WebsvcService.php';

class RepositoryService extends WebsvcService
{

    protected $categoryDO = "repository.category.do.CategoryDO";
    protected $categoryTransDO = "repository.category.do.CategoryTranslateDO";
    protected $applicationDO = "repository.application.do.ApplicationDO";
    protected $applicationTransDO = "repository.application.do.ApplicationTranslateDO";
    protected $releaseDO = "repository.release.do.ReleaseDO";
    protected $installLogDO = "repository.install.do.InstallLogDO";
    protected $pictureDO = "picture.do.PictureDO";
    protected $repositorySettingDO = "myaccount.do.PreferenceDO";
    protected $repositorySettingTransDO = "repository.setting.do.SettingTranslateDO";
    protected $applicationVersionDO = "repository.application.do.ApplicationVersionDO";

    public function fetchRepoInfo()
    {
        $searchRule = "[user_id]='0' AND [name] LIKE 'repo_%'";
        $dataObj = Openbizx::getObject($this->repositorySettingDO);
        $resultRecords = $dataObj->directfetch($searchRule);
        $prefRecord = array();
        foreach ($resultRecords as $record) {
            $prefRecord["_" . $record['name']] = $record["value"];
        }
        //try to translate cats        
        $lang = $_REQUEST['lang'];
        if ($lang) {
            $settingTransDO = Openbizx::getObject($this->repositorySettingTransDO, 1);
            $transFields = array('repo_name', 'repo_desc');

            $recordId = $record['Id'];
            $transRec = $settingTransDO->fetchOne("[lang]='$lang'");
            if ($transRec) {
                foreach ($transFields as $field) {
                    $prefRecord['_' . $field] = $transRec[$field] ? $transRec[$field] : $prefRecord['_' . $field];
                }
            }
        }
        return $prefRecord;
    }

    public function recordInstallLog($app_id, $release_id, $siteurl, $operator)
    {
        $remote_ip = $_SERVER['REMOTE_ADDR'];
        $logRec = array();
        $logRec['app_id'] = $app_id;
        $logRec['release_id'] = $release_id;
        $logRec['remote_ip'] = $remote_ip;
        $logRec['remote_siteurl'] = $siteurl;
        $logRec['remote_operator'] = $operator;
        $dataObj = Openbizx::getObject($this->installLogDO);
        $dataObj->insertRecord($logRec);
        return true;
    }

    public function fetchAppList($ids = array())
    {
        $appIds = implode(",", $ids);
        $searchRule = "[status]=1 AND [Id] IN ($appIds)";
        $dataObj = Openbizx::getObject($this->applicationVersionDO, 1);
        $resultRecords = $dataObj->directFetch($searchRule);
        $resultSet = array();
        foreach ($resultRecords as $record) {
            $resultSet[] = $record;
        }
        $resultSet = $this->translateAppList($resultSet);
        return $resultSet;
    }

    public function fetchNewAppRelease($timestamp)
    {
        $searchRule = "[release_time] > '$timestamp' AND [status]='1' ";
        $dataObj = Openbizx::getObject($this->applicationDO, 1);
        $resultRecords = $dataObj->directFetch($searchRule);
        $resultSet = array();
        foreach ($resultRecords as $record) {
            $resultSet[] = $record;
        }
        $resultSet = $this->translateAppList($resultSet);
        return $resultSet;
    }

    public function fetchAppInfo($id = null)
    {
        $searchRule = "[status]=1 AND [release_time] < NOW() AND [Id]='$id'";
        $dataObj = Openbizx::getObject($this->applicationDO, 1);
        $result = $dataObj->fetchOne($searchRule);
        if ($result) {
            $result = $result->toArray();
            $result = $this->translateAppInfo($result);
        } else {
            $result = array();
        }
        return $result;
    }

    public function fetchAppPics($id = null)
    {
        $searchRule = "[type]='application' AND [foreign_id]='$id'";
        $dataObj = Openbizx::getObject($this->pictureDO, 1);
        $resultRecords = $dataObj->directfetch($searchRule);
        $resultSet = array();
        foreach ($resultRecords as $record) {
            $resultSet[] = $record;
        }
        return $resultSet;
    }

    public function fetchAppLatestRelease($id = null)
    {
        $searchRule = "[app_id]='$id'";
        $sortRule = "[Id] DESC";
        $dataObj = Openbizx::getObject($this->releaseDO, 1);
        $results = $dataObj->directfetch($searchRule, null, null, $sortRule);
        if ($results) {
            $result = $results[0];
        } else {
            $result = array();
        }
        return $result;
    }

    public function fetchFeaturedApps($param = array())
    {
        $searchRule = "[status]=1 AND [release_time] < NOW() AND [featured]=1";
        $sortRule = $param['sortRule'];
        if (!$sortRule) {
            $sortRule = "[release_time] DESC";
        }
        $userSearchRule = $param['searchRule'];
        $startItem = $param['startItem'];
        $range = $param['range'];
        if ($userSearchRule) {
            $searchRule .= " AND " . $userSearchRule;
        }
        $dataObj = Openbizx::getObject($this->applicationDO, 1);
        $dataObj->setSearchRule($searchRule);
        $dataObj->setSortRule($sortRule);
        $dataObj->setLimit($range, $startItem);
        $resultRecords = $dataObj->fetch();
        $resultSet = array();
        foreach ($resultRecords as $record) {
            $resultSet[] = $record;
        }
        $resultSet = $this->translateAppList($resultSet);
        $result['data'] = $resultSet;
        $result['totalRecords'] = $dataObj->count();
        return $result;
    }

    public function fetchApplications($cat_id = null, $param = array())
    {
        $searchRule = "[status]=1 AND [release_time] < NOW() ";
        if ($cat_id) {
            $searchRule .= " AND [cat_id]=$cat_id ";
        }
        $sortRule = $param['sortRule'];
        if (!$sortRule) {
            $sortRule = "[pkg_release_time] DESC";
        }
        $userSearchRule = $param['searchRule'];
        $startItem = $param['startItem'];
        $range = $param['range'];
        if ($userSearchRule) {
            $searchRule .= " AND " . $userSearchRule;
        }
        $dataObj = Openbizx::getObject($this->applicationDO, 1);
        $dataObj->setSearchRule($searchRule);
        $dataObj->setSortRule($sortRule);
        $dataObj->setLimit($range, $startItem);
        $resultRecords = $dataObj->fetch();
        $resultSet = array();
        foreach ($resultRecords as $record) {
            $resultSet[] = $record;
        }
        $resultSet = $this->translateAppList($resultSet);
        $result['data'] = $resultSet;
        $result['totalRecords'] = $dataObj->count();
        return $result;
    }

    protected function translateAppList($resultSet)
    {
        //try to translate cats
        $lang = $_REQUEST['lang'];
        if ($lang) {
            $resultSetTrans = $resultSet;
            $resultSet = array();
            foreach ($resultSetTrans as $record) {
                $resultSet[] = $this->translateAppInfo($record);
            }
        }
        return $resultSet;
    }

    protected function translateAppInfo($result)
    {
        $lang = $_REQUEST['lang'];
        if ($lang) {
            $applicationTransDO = Openbizx::getObject($this->applicationTransDO, 1);
            $transFields = array('name', 'description', 'author', 'type');
            $recordId = $result['Id'];
            $transRec = $applicationTransDO->fetchOne("[repo_app_id]='$recordId' AND [lang]='$lang'");
            if ($transRec) {
                foreach ($transFields as $field) {
                    $result[$field] = $transRec[$field] ? $transRec[$field] : $result[$field];
                }
            }
            //translate cate name
            $categoryTransDO = Openbizx::getObject($this->categoryTransDO, 1);
            $catId = $result['category_id'];
            $categoryTransRec = $categoryTransDO->fetchOne("[repo_cat_id]='$catId' AND [lang]='$lang'");
            if ($categoryTransRec) {
                $result['category_name'] = $categoryTransRec['name'];
            }
        }
        return $result;
    }

    public function fetchCategories()
    {
        $searchRule = "[publish]=1";
        $dataObj = Openbizx::getObject($this->categoryDO, 1);
        $resultRecords = $dataObj->directfetch($searchRule);
        $resultSet = array();
        foreach ($resultRecords as $record) {
            $resultSet[] = $record;
        }
        //try to translate cats
        $lang = $_REQUEST['lang'];
        if ($lang) {
            $resultSetTrans = $resultSet;
            $resultSet = array();
            $categoryTransDO = Openbizx::getObject($this->categoryTransDO, 1);
            $transFields = array('name', 'description');
            foreach ($resultSetTrans as $record) {
                $recordId = $record['Id'];
                $transRec = $categoryTransDO->fetchOne("[repo_cat_id]='$recordId' AND [lang]='$lang'");
                if ($transRec) {
                    foreach ($transFields as $field) {
                        $record[$field] = $transRec[$field] ? $transRec[$field] : $record[$field];
                    }
                }
                $resultSet[] = $record;
            }
        }
        return $resultSet;
    }

}
