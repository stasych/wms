<?
require_once $_SERVER['DOCUMENT_ROOT'].'/src/classes/CCore/CCore.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/src/utils/custom_utils.php';
edebug(true);
$project_id = 1;
$warehouse_id = 1;
$core = new CCore($warehouse_id,$project_id);

$n = 11;
$received_task_id = 1;
while(--$n)
{
    $k = 100*$n;
    $product_id = $n;
    $price = 100.6543;
    $nds = 0.18;
    $nds_sum = $price*$nds;
    $address_id = 1;
    $gtd = "GTD 00000{$k}";
    $country_id = 1;
    $tn_id = $k;
    try
    {
        $core->create_new_item( $received_task_id,$product_id,$price,$nds,$nds_sum,$address_id,$gtd,$country_id,$tn_id);
    }
    catch(Exception $e)
    {
        dumpEx($e,"Exception");
    }
}