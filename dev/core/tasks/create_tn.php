<?
require_once $_SERVER['DOCUMENT_ROOT'].'/src/classes/CCore/CCore.php';
require_once $_SERVER[ 'DOCUMENT_ROOT' ] . '/src/utils/custom_utils.php';
edebug(true);
$project_id = 1;
$warehouse_id = 1;
$core = new CCore($project_id,$warehouse_id);

//Test case 1
try
{
    $receive_task_id = 10;
    $tn_number = "TN 100";
    $invoice_number = "INVOICE 100";
    $tn_sum = 100.22;
    $tn_date= time();
    $supplier_id = 1;
    $nds = 0.18;
    $nds_sum = false;
    $item_id = false;
    $res = $core->create_tn($receive_task_id,$tn_number,$invoice_number,$tn_sum,$tn_date,$supplier_id,$nds,$nds_sum,$item_id);
    dumpEx($res, "Test case 1. Create new tn result");
}
catch(Exception $e)
{
    dumpEx($e, 'Exception. Test case 1');
}

//Test case 2
try
{
    $receive_task_id = 10;
    $tn_number = "TN 100";
    $invoice_number = "UPDATED INVOICE 100";
    $tn_sum = 100.22;
    $tn_date= time();
    $supplier_id = 1;
    $nds = 0.18;
    $nds_sum = false;
    $item_id = false;
    $res = $core->create_tn($receive_task_id,$tn_number,$invoice_number,$tn_sum,$tn_date,$supplier_id,$nds,$nds_sum,$item_id);
    dumpEx($res, "Test case 1. Create new tn result");
}
catch(Exception $e)
{
    dumpEx($e, 'Exception. Test case 1');
}