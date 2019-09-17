<?
require_once $_SERVER['DOCUMENT_ROOT'].'/src/classes/CCore/CCore.php';
require_once $_SERVER[ 'DOCUMENT_ROOT' ] . '/src/utils/custom_utils.php';
edebug(true);
$project_id = 1;
$warehouse_id = 1;
$core = new CCore($project_id,$warehouse_id);

//Test case 1
$product_id = 1;
try
{
    $res = $core->get_product_by_id($product_id);
    dumpEx($res, "Test case 1. Product with ID: {$product_id} ");
}
catch(Exception $e)
{
    dumpEx($e, 'Exception. Test case 1');
}

//Test case 2
$product_id = 1000000;
try
{
    $res = $core->get_product_by_id($product_id);
    dumpEx($res, "Test case 2. Product with ID: {$product_id} ");
}
catch(Exception $e)
{
    dumpEx($e, 'Exception. Test case 2');
}