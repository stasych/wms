<?
require_once $_SERVER['DOCUMENT_ROOT'].'/src/classes/CCore/CCore.php';
require_once $_SERVER[ 'DOCUMENT_ROOT' ] . '/src/utils/custom_utils.php';
edebug(true);
$project_id = 1;
$warehouse_id = 1;
$core = new CCore($project_id,$warehouse_id);

//Test case 1
$receive_task_id = 10;
try
{
    $res = $core->get_items_by_receive_task( $receive_task_id);
    dumpEx($res, "Test case 1. Get items for receive_task_id: {$receive_task_id}");
}
catch(Exception $e)
{
    dumpEx($e, 'Exception. Test case 1');
}