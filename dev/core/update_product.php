<?
require_once $_SERVER['DOCUMENT_ROOT'].'/src/classes/CCore/CCore.php';
require_once $_SERVER[ 'DOCUMENT_ROOT' ] . '/src/utils/custom_utils.php';
edebug(true);
$project_id = 1;
$warehouse_id = 1;
$core = new CCore($project_id,$warehouse_id);

//Test case 1
$product_id = 1;
$fields_values = [
    'BARCODE'=>111
];
dumpEx($fields_values,"Update product_id: {$product_id} with values");
try
{
    $res = $core->update_product($product_id,$fields_values);
    dumpEx($res, "Test case 1. Product_id: {$product_id} updated");
}
catch(Exception $e)
{
    dumpEx($e, 'Exception. Test case 1');
}