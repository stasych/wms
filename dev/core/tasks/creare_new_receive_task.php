<?
require_once $_SERVER['DOCUMENT_ROOT'].'/src/classes/CCore/CCore.php';
require_once $_SERVER[ 'DOCUMENT_ROOT' ] . '/src/utils/custom_utils.php';
//edebug(true);
$project_id = 1;
$warehouse_id = 1;
$core = new CCore($project_id,$warehouse_id);

$user_id = 1;
//Test case 1
try
{
    $user_id = $core->create_new_receive_task($user_id)->data;
    dumpEx($user_id, "Test case 1. New receive task created");
}
catch(Exception $e)
{
    dumpEx($e, 'Exception. Test case 1');
}

//Test case 2
try
{
    $date_receive = strtotime(date('Y-m-d H:i:s', strtotime(' +1 day')));
    $user_id = $core->create_new_receive_task($user_id,$date_receive)->data;
    dumpEx($user_id, "Test case 2. New receive task created");
}
catch(Exception $e)
{
    dumpEx($e, 'Exception. Test case 2');
}