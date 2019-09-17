<?
require_once $_SERVER[ 'DOCUMENT_ROOT' ] . '/src/classes/queue/queue.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/src/classes/events_factory/OrderFactory.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/src/utils/custom_utils.php';
edebug(true);
$queue = new MySQLQueue(new OrderFactory);

#Test case 1
$event_data = [
    'ORDER_ID'=>102,
];
$cancel_data = [
    'EVENT_TYPE'=>'cancel_order',
    'PROJECT_ID'=>1,
    'WAREHOUSE_ID'=>1,
    'DATA'=>$event_data
];
dumpEx($cancel_data,"Test case 1. Cancel data");

try
{
    $queue->push($cancel_data);
    $queue->pop_event()->exec();
    dumpEx('Executed',"Test case 1. Executed");
}
catch(Exception $e)
{
    dumpEx($e, "Test case 1");
}