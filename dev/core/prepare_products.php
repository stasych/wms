<?
require_once $_SERVER['DOCUMENT_ROOT'].'/src/classes/CCore/CCore.php';
require_once $_SERVER[ 'DOCUMENT_ROOT' ] . '/src/utils/custom_utils.php';
//edebug(true);
$project_id = 1;
$warehouse_id = 1;
$core = new CCore($project_id,$warehouse_id);

$products = [['PRODUCT_ID'=>10,'AMOUNT'=>10],
             ['PRODUCT_ID'=>2,'AMOUNT'=>10],
             ['PRODUCT_ID'=>3,'AMOUNT'=>4],
            ];
dumpEx($products,'Input products');
try
{
    $res = $core->prepare_products($products);
    dumpEx($res->data,'Splited products by availability');
}
catch(Exception $e)
{
    dumpEx($e,'Exception');
}