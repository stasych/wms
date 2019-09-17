<?
require_once $_SERVER['DOCUMENT_ROOT'].'/src/classes/CCore/CCore.php';
require_once $_SERVER[ 'DOCUMENT_ROOT' ] . '/src/utils/custom_utils.php';
edebug(true);
$project_id = 1;
$warehouse_id = 1;
$core = new CCore($project_id,$warehouse_id);

//Test case 1
try
{
    $task_id = 22;
    $res = $core->complete_receive_task($task_id,'SUCCESS');
    dumpEx($res, "Test case 1. Complete receive_task_id:{$task_id} result");
}
catch(Exception $e)
{
    dumpEx($e, 'Exception. Test case 1');
}