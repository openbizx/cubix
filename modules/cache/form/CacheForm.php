<?php

/**
 * Openbizx Cubi Application Platform
 *
 * LICENSE http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 *
 * @package   cubi.cache.form
 * @copyright Copyright (c) 2005-2011, Openbiz Technology LLC
 * @license   http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 * @link      http://code.google.com/p/openbiz-cubi/
 * @version   $Id: CacheForm.php 5301 2013-02-26 07:38:43Z hellojixian@gmail.com $
 */
use Openbizx\Openbizx;
use Openbizx\Object\ObjectFactoryHelper;
use Openbizx\Easy\EasyFormGrouping;

class CacheForm extends EasyFormGrouping
{

    public $modeStatus;

    public function clear()
    {
        if ($id == null || $id == '')
            $id = Openbizx::$app->getClientProxy()->getFormInputs('_selectedId');

        $selIds = Openbizx::$app->getClientProxy()->getFormInputs('row_selections', false);
        if ($selIds == null)
            $selIds[] = $id;
        foreach ($selIds as $id) {
            $data = $this->getRecoredDetail($id);
            if (substr($item['Id'], 0, 7) == 'APPDATA') {
                $this->DeleteDirectory($data['path'], true);
            } else {
                $this->DeleteDirectory($data['path'], false);
            }
        }
        $this->updateForm();
    }

    public function clearAll()
    {
        $data = $this->fetchDataGroup(true);
        foreach ($data as $group) {
            foreach ($group as $item) {
                if (substr($item['Id'], 0, 7) == 'APPDATA') {
                    $this->DeleteDirectory($item['path'], true);
                } else {
                    $this->DeleteDirectory($item['path'], false);
                }
            }
        }
        $this->updateForm();
    }

    public function clearBin()
    {
        $data = $this->getRecoredDetail($this->recordId);
        $this->DeleteDirectory($data['path']);
        $this->updateForm();
    }

    public function outputAttrs()
    {
        $result = parent::outputAttrs();
        $file = Openbizx::$app->getModulePath() . DIRECTORY_SEPARATOR . "service" . DIRECTORY_SEPARATOR . "cacheService.xml";
        if (!is_file($file)) {
            return;
        }
        $configArr = ObjectFactoryHelper::getXmlArray($file);

        $this->modeStatus = $configArr["PLUGINSERVICE"]["CACHESETTING"]["ATTRIBUTES"]["MODE"];
        if ($this->modeStatus == 'Enabled') {
            $result['status'] = 'Enabled';
            $this->modeStatus = 'Enabled';
        } else {
            $result['status'] = 'Disabled';
            $this->modeStatus = 'Disabled';
        }
        return $result;
    }

    public function switchMode()
    {
        $file = Openbizx::$app->getModulePath() . DIRECTORY_SEPARATOR . "service" . DIRECTORY_SEPARATOR . "cacheService.xml";
        if (!is_file($file)) {
            return;
        }
        $configArr = ObjectFactoryHelper::getXmlArray($file);

        $this->modeStatus = $configArr["PLUGINSERVICE"]["CACHESETTING"]["ATTRIBUTES"]["MODE"];
        if ($this->modeStatus == 'Enabled') {
            $newMode = 'Disabled';
        } else {
            $newMode = 'Enabled';
        }
        $configData = file_get_contents($file);
        $configData = preg_replace("/CacheSetting\sMode\=\"(.*?)\"/si", "CacheSetting Mode=\"$newMode\"", $configData);
        file_put_contents($file, $configData);

        $this->updateForm();
    }

    private function getRecoredDetail($recId)
    {
        $data = $this->fetchDataGroup(true);
        foreach ($data as $group) {
            foreach ($group as $item) {
                if ($item["Id"] == $recId) {
                    return $item;
                }
            }
        }
    }

    public function fetchDataGroup($lite = false)
    {
        $results = array();

        //Application data
        $appdata = array();
        $i = 0;
        if (is_array(glob(CUBI_CACHE_DATA_PATH . DIRECTORY_SEPARATOR . "*", GLOB_ONLYDIR))) {
            foreach (glob(CUBI_CACHE_DATA_PATH . DIRECTORY_SEPARATOR . "*", GLOB_ONLYDIR) as $dir) {
                $appdata[$i]['Id'] = "APPDATA_" . $i;
                $appdata[$i]['name'] = ucwords(basename($dir));
                if (!$lite) {
                    $appdata[$i]['space'] = $this->GetSpaceUsage($dir);
                    $appdata[$i]['items'] = $this->GetChildItems($dir);
                }
                $appdata[$i]['path'] = $dir;
                $i++;
            }
        }
        if (!$lite) {
            $results["Application Data"] = $this->dataPanel->renderTable($appdata);
        } else {
            $results["Application Data"] = $appdata;
        }
        //System metadata
        $metadata = array();
        $metadata[0]["Id"] = "Metadata";
        $metadata[0]["name"] = "Metadata";
        if (!$lite) {
            $metadata[0]["space"] = $this->GetSpaceUsage(OPENBIZ_CACHE_METADATA_PATH);
            $metadata[0]["items"] = $this->GetChildItems(OPENBIZ_CACHE_METADATA_PATH);
        }
        $metadata[0]['path'] = OPENBIZ_CACHE_METADATA_PATH;
        if (!$lite) {
            $results["System Metadata"] = $this->dataPanel->renderTable($metadata);
        } else {
            $results["System Metadata"] = $metadata;
        }
        //Complied Template 
        $tempdata = array();

        $i = 0;
        foreach (glob(OPENBIZ_SMARTY_CPL_PATH . DIRECTORY_SEPARATOR . "*", GLOB_ONLYDIR) as $dir) {
            $tempdata[$i]['Id'] = "TEMPDATA_" . $i;
            $tempdata[$i]['name'] = ucwords(basename($dir));


            if (!$lite) {
                $tempdata[$i]['space'] = $this->GetSpaceUsage($dir);
                $tempdata[$i]['items'] = $this->GetChildItems($dir);
            }
            $tempdata[$i]['path'] = $dir;
            $i++;
        }

        if (!$lite) {
            $results["Complied Template"] = $this->dataPanel->renderTable($tempdata);
        } else {
            $results["Complied Template"] = $tempdata;
        }
        $this->recordId = "APPDATA_1";
        return $results;
    }

    private function getSpaceUsage($dir)
    {
        $size = 0;
        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir)) as $file) {
            $size+=$file->getSize();
        }
        return sprintf("%.1f KB", ($size / 1024));
    }

    private function getChildItems($dir)
    {
        $dir_iterator = new RecursiveDirectoryIterator($dir);
        $iterator = new RecursiveIteratorIterator($dir_iterator, RecursiveIteratorIterator::SELF_FIRST);
        // could use CHILD_FIRST if you so wish
        $i = 0;
        foreach ($iterator as $file) {

            $i++;
        }
        return $i . " Items";
    }

    private function deleteDirectory($dir, $deleteItSelf = false)
    {
        $iterator = new RecursiveDirectoryIterator($dir);
        foreach (new RecursiveIteratorIterator($iterator, RecursiveIteratorIterator::CHILD_FIRST) as $file) {
            if ($file->isDir()) {
                @rmdir($file->getPathname());
            } else {
                @unlink($file->getPathname());
            }
        }
        if ($deleteItSelf) {
            @rmdir($dir);
        }
        return true;
    }

}
