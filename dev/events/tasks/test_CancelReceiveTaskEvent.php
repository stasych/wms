<?
require_once $_SERVER[ 'DOCUMENT_ROOT' ] . '/src/classes/queue/queue.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/src/classes/events_factory/TaskFactory.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/src/utils/custom_utils.php';
edebug(true);
$queue = new MySQLQueue(new TaskFactory);

#Test case 1
$event_data = [
    'TASK_ID'=>36,
];
$data = [
    'EVENT_TYPE'=>'cancel_receive_task',
    'PROJECT_ID'=>1,
    'WAREHOUSE_ID'=>1,
    'DATA'=>$event_data
];
dumpEx($data,"Test case 1. Cancel receive task");

try
{
    $event_id = $queue->push($data)->data;
    dumpEx($event_id);
    $queue->pop_event()->exec();
    $event_result_data = $queue->get_event_result($event_id);
    dumpEx($event_result_data,"Test case 1. Event result data");
}
catch(Exception $e)
{
    dumpEx($e, "Test case 1");
}