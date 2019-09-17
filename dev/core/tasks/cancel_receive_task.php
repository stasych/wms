<?
require_once $_SERVER['DOCUMENT_ROOT'].'/src/classes/CCore/CCore.php';
require_once $_SERVER[ 'DOCUMENT_ROOT' ] . '/src/utils/custom_utils.php';
//edebug(true);
$project_id = 1;
$warehouse_id = 1;
$core = new CCore($project_id,$warehouse_id);

$task_id = 10;
//Test case 1
try
{
    $core->cancel_receive_task($task_id);
    dumpEx('Executed', "Test case 1. Cancel receive task_id: {$task_id}");
}
catch(Exception $e)
{
    dumpEx($e, 'Exception. Test case 1');
}