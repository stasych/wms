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
    $statuses_ids  = $statuses->get_ids('ORDER',['PENDING_PRODUCTS','PENDING_COMPLECTATION','IN_COMPLECTATION']);
    dumpEx($statuses_ids, "Test case 1. Ids");
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
    $status_id = 1;
    $statuses_ids  = $statuses->get_ids('ORDER',['PENDING_PRODUCTS','PENDING_COMPLECTATION','IN_COMPLECTATION','ASDF']);
    dumpEx($statuses_ids, "Test case 2. Ids");
}
catch(Exception $e)
{
    dumpEx($e, 'Exception: Test case 2');
}