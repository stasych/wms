<?
require_once $_SERVER['DOCUMENT_ROOT'].'/src/classes/CCore/CCore.php';
require_once $_SERVER[ 'DOCUMENT_ROOT' ] . '/src/utils/custom_utils.php';
//edebug(true);
$project_id = 1;
$warehouse_id = 1;
$core = new CCore($project_id,$warehouse_id);

# Test case 1
$order_id = 1;
try
{
    $res = $core->get_order_status($order_id);
    $status = $res->data['ORDER_STATUS']->title;
    dumpEx(get_object_vars($res),"Test case 1. Status '{$status}' for order_id: {$order_id}");
}
catch(Exception $e)
{
    dumpEx($e,"Exception. Test case 1");
}

# Test case 2
$order_id = 100;
try
{
    $res = $core->get_order_status($order_id);
    $status = $res->data['ORDER_STATUS']->title;
    dumpEx(get_object_vars($res),"Test case 2. Status '{$status}' for order_id: {$order_id}");
}
catch(Exception $e)
{
    dumpEx($e,"Exception. Test case 2");
}


# Test case 3
$order_id = 1000;
try
{
    $res = $core->get_order_status($order_id);
    $status = $res->data['ORDER_STATUS']->title;
    dumpEx(get_object_vars($res),"Test case 3. Status '{$status}' for order_id: {$order_id}");
}
catch(Exception $e)
{
    dumpEx($e,"Exception. Test case 3");
}
