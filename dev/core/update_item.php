<?
require_once $_SERVER['DOCUMENT_ROOT'].'/src/classes/CCore/CCore.php';
require_once $_SERVER[ 'DOCUMENT_ROOT' ] . '/src/utils/custom_utils.php';
edebug(true);
$project_id = 1;
$warehouse_id = 1;
$core = new CCore($project_id,$warehouse_id);

//Test case 1
$item_id = 104;
try
{
    $res = $core->update_item($item_id,false, false, 1000);
    dumpEx($res, "Test case 1. Update item");
}
catch(Exception $e)
{
    dumpEx($e, 'Exception. Test case 1');
}