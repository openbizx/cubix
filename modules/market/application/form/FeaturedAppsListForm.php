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
 * @version   $Id: FeaturedAppsListForm.php 3363 2012-05-31 06:04:56Z rockyswen@gmail.com $
 */
use Openbizx\Openbizx;

include_once 'AppListForm.php';

class FeaturedAppsListForm extends AppListForm
{

    public function fetchDataSet()
    {
        parent::fetchDataSet();
        $svc = Openbizx::getService("market.lib.PackageService");
        $resultSet = array();

        $repo_uri = $this->getDefaultRepoURI();
        $params = array(
            "sortRule" => $this->sortRule,
            "searchRule" => $this->remoteSearchRule,
            "range" => $this->range,
            "startItem" => ($this->currentPage - 1) * $this->range
        );

        $appList = $svc->discoverFeaturedApps($repo_uri, $params);
        if (is_array($appList['data'])) {
            foreach ($appList['data'] as $appInfo) {
                $appInfo['icon'] = $repo_uri . $appInfo['icon'];
                $resultSet[] = $appInfo;
            }
        }
        $this->totalRecords = $appList['totalRecords'];
        if ($this->range && $this->range > 0)
            $this->totalPages = ceil($this->totalRecords / $this->range);

        return $resultSet;
    }

}

