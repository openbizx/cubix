<?php
require_once('app_init.php');
if (!CUBI_APPBUILDER)
{
    echo "Sorry, AppBuilder/MetaEdit disable.";
    exit;
}

$modsvc = Openbizx::getObject("system.lib.ModuleService");
if(!$modsvc->isModuleInstalled('appbuilder'))
{
    echo "Sorry, AppBuilder is not installed.";
    exit;	
}
if($_GET['action']=='launch')
{
	$url = OPENBIZ_APP_INDEX_URL."/appbuilder/dashboard";
	header("LOCATION: $url");
	exit;
}
$metaobj = $_GET['metaobj'];
$url = OPENBIZ_APP_INDEX_URL."/appbuilder/xml_edit/metaobj=".$metaobj;
header("LOCATION: $url");
?>