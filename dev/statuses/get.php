<?
require_once $_SERVER['DOCUMENT_ROOT'] . "/src/classes/CStatuses/CStatuses.php";
require_once $_SERVER[ 'DOCUMENT_ROOT' ] . '/src/utils/custom_utils.php';

# Test case 1
try
{
    $project_id = 1;
    $statuses = new CStatuses($project_id);
    $res  = $statuses->get('ORDER', 'PENDING');
    dumpEx(get_object_vars($res), 'Test case 1: get Status object');
}
catch(Exception $e)
{
    dumpEx($e, 'Exception: Test case 1');
}

# Test case 2
try
{
    $project_id = 2;
    $statuses = new CStatuses($project_id);
    $res  = $statuses->get('ORDER', 'PENDING');
    dumpEx(get_object_vars($res), 'Test case 2: get Status object');
}
catch(Exception $e)
{
    dumpEx($e, 'Exception: wrong id,  Test case 2');
}

# Test case 3
try
{
    $project_id = 1;
    $statuses = new CStatuses($project_id);
    $res  = $statuses->get('TEST', 'TEST');
    dumpEx(get_object_vars($res), 'Test case 3: get Status object by wrong entity');
}
catch(Exception $e)
{
    dumpEx($e, 'Exception: wrong entity,  Test case 3');
}

# Test case 4
try
{
    $project_id = 1;
    $statuses = new CStatuses($project_id);
    $res  = $statuses->get('ORDER', 'TEST');
    dumpEx(get_object_vars($res), 'Test case 4: get Status object by wrong type');
}
catch(Exception $e)
{
    dumpEx($e, 'Exception: wrong type,  Test case 4');
}