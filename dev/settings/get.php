<?
require_once $_SERVER['DOCUMENT_ROOT'] . "/src/classes/CSettings/CSettings.php";
require_once $_SERVER[ 'DOCUMENT_ROOT' ] . '/src/utils/custom_utils.php';
edebug(true);

$project_id = 1;
$warehouse_id = 1;

try
{
    $settings = new CSettings($project_id,$warehouse_id);
    dumpEx($settings,"Settings for project_id:{$project_id}, warehouse_id:{$warehouse_id}");
    
    $key = "DEFAULT";
    dumpEx($settings->get($key),"Value for key:{$key}");
    $key = "DEFAULT_NDS";
    dumpEx($settings->get($key),"Value for key:{$key}");
    $key = "DEFAULT_FIELD";
    dumpEx($settings->get($key)?:'NO FIELD',"Value for key:{$key}");
}
catch(Exception $e)
{
    dumpEx($e,"Settings error");
}

