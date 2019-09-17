<?
require_once $_SERVER['DOCUMENT_ROOT'].'/src/classes/CCore/CCore.php';
require_once $_SERVER[ 'DOCUMENT_ROOT' ] . '/src/utils/custom_utils.php';
//edebug(true);
$project_id = 1;
$warehouse_id = 1;
$core = new CCore($project_id,$warehouse_id);

#Test case 1
$order_id = 1;
try
{
    $res = $core->cancel_products($order_id);
    dumpEx($res->msg, 'Test case 1');
}
catch (Exception $e)
{
    dumpEx($e,'Exception. Test case 1');
    
}

//#Test case 2
//$order_id = 100;
//try
//{
//    $res = $core->cancel_products($order_id);
//    dumpEx($res->msg, 'Test case 2');
//}
//catch (Exception $e)
//{
//    dumpEx($e,'Exception. Test case 2');
//
//}