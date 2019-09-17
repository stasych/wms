<?
require_once $_SERVER['DOCUMENT_ROOT'].'/src/classes/CCore/CCore.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/src/utils/custom_utils.php';

$project_id = 1;
$warehouse_id = 1;
$core = new CCore($warehouse_id,$project_id);

$n = 101;
while(--$n)
{
    $id = $n;
    $brand = "BRAND".$n%10;
    $sku = $n*100;
    $name = "NAME {$brand} {$sku}";
    $barcode = $n*111;
    dumpEx([$brand,$sku,$name,$barcode],"Iteration {$n}");
    try
    {
        $core->create_new_product( $id,
                                   $brand,
                                   $sku,
                                   $name,
                                   $barcode );
    }
    catch(Exception $e)
    {
        dumpEx($e,"Exception");
    }
}