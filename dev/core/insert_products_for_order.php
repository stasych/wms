<?
require_once $_SERVER['DOCUMENT_ROOT'].'/src/classes/CCore/CCore.php';
require_once $_SERVER[ 'DOCUMENT_ROOT' ] . '/src/utils/custom_utils.php';
//edebug(true);
$project_id = 1;
$warehouse_id = 1;
$core = new CCore($project_id,$warehouse_id);

# Test case 1
$purchases =[];
$purchases[] = ['PRODUCT_ID'=>1,'AMOUNT'=>3,'STATUS'=>'PENDING'];
$purchases[] = ['PRODUCT_ID'=>2,'AMOUNT'=>1,'STATUS'=>'RESERVED'];
try
{
    $order_id = 1;
    $res = $core->insert_products_for_order($purchases,$order_id);
    dumpEx($res, 'Test case 1');
}
catch(Exception $e)
{
    dumpEx($e, 'Exception : Test case 1');
}