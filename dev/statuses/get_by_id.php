<?
require_once $_SERVER['DOCUMENT_ROOT'] . "/src/classes/CStatuses/CStatuses.php";
require_once $_SERVER[ 'DOCUMENT_ROOT' ] . '/src/utils/custom_utils.php';
//edebug(true);

# Test case 1
try
{
    $project_id = 1;
    $statuses = new CStatuses($project_id);
    $status_id = 1;
    $res  = $statuses->get_by_id($status_id);
    dumpEx(get_object_vars($res), "Test case 1: get by id={$status_id}");
}
catch(Exception $e)
{
    dumpEx($e, 'Exception: Test case 1');
}

# Test case 2
try
{
    $project_id = 1;
    $statuses = new CStatuses($project_id);
    $status_id = 100;
    $res  = $statuses->get_by_id($status_id);
    dumpEx(get_object_vars($res), "Test case 2: get by id={$status_id}");
}
catch(Exception $e)
{
    dumpEx($e, 'Exception: Test case 2');
}