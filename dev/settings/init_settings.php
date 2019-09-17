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
}
catch(Exception $e)
{
    dumpEx($e,"Settings error");
}

try
{
    $settings2 = new CSettings($project_id,false);
    dumpEx($settings2,"Settings for project_id:{$project_id}");
}
catch(Exception $e)
{
    dumpEx($e,"Settings error");
}

