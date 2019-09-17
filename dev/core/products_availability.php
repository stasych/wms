<?
require_once $_SERVER['DOCUMENT_ROOT'].'/src/classes/CCore/CCore.php';
require_once $_SERVER[ 'DOCUMENT_ROOT' ] . '/src/utils/custom_utils.php';
//edebug(true);
$project_id = 1;
$warehouse_id = 1;
$core = new CCore($project_id,$warehouse_id);

# Test case 1
$products = [1,2,3,4,5,6,7,8,9,10];
try
{
    $res = $core->products_availability($products);
    dumpEx($res->data, 'Test case 1. Availability of products:'.implode(',',$products));
}
catch(Exception $e)
{
    dumpEx($e, 'Exception : Test case 1');
}
