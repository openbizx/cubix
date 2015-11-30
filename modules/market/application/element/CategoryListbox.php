<?php

/**
 * Openbizx Cubi Application Platform
 *
 * LICENSE http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 *
 * @package   cubi.market.application.element
 * @copyright Copyright (c) 2005-2011, Openbiz Technology LLC
 * @license   http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 * @link      http://code.google.com/p/openbiz-cubi/
 * @version   $Id: CategoryListbox.php 3363 2012-05-31 06:04:56Z rockyswen@gmail.com $
 */
use Openbizx\Openbizx;
use Openbizx\Easy\Element\Listbox;

class CategoryListbox extends Listbox
{

    public function getFromList(&$list, $selectFrom = null)
    {

        if ($_POST['fld_repo_id']) {
            $repoRec = Openbizx::getObject("market.repository.do.RepositoryDO")->fetchById((int) $_POST['fld_repo_id']);
        } else {
            $repoRec = Openbizx::getObject("market.repository.do.RepositoryDO")->fetchOne("[status]=1");
        }
        $repo_uri = $repoRec['repository_uri'];

        $svc = Openbizx::getService("market.lib.PackageService");
        $catList = $svc->discoverCategory($repo_uri);
        if (is_array($catList)) {
            foreach ($catList as $cat) {
                $list[] = array(
                    "val" => $cat['Id'],
                    "txt" => $cat['name'],
                );
            }
        }
        return;
    }

}

