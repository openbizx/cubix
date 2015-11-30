<?PHP

//$start = (float) array_sum(explode(' ',microtime())); 
define("OPENBIZ_USE_CUSTOM_SESSION_HANDLER", true);
include_once("app_init.php");

$app = new Openbizx\Application();
$app->run();
/*
$end = (float) array_sum(explode(' ',microtime()));
echo "Processing time: ". sprintf("%.4f", ($end-$start))." seconds"; 
*/
