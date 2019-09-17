<?
require_once $_SERVER['DOCUMENT_ROOT'].'/src/classes/CCore/CCore.php';
require_once $_SERVER[ 'DOCUMENT_ROOT' ] . '/src/utils/custom_utils.php';
edebug(true);
$project_id = 1;
$warehouse_id = 1;
$core = new CCore($project_id,$warehouse_id);

//Test case 1
$sku = '1101';
try
{
    $res = $core->api_get_products_by_sku($sku);
    dumpEx($res, "Test case 1. Products from inner api for sku: {$sku}");
}
catch(Exception $e)
{
    dumpEx($e, 'Exception. Test case 1');
}