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
    $order_id = 3;
    $res = $core->refresh_order_complectation($order_id);
    dumpEx($res, "Test case 1. Check order_id: {$order_id} products status");
}
catch(Exception $e)
{
    dumpEx($e, 'Exception. Test case 1');
}