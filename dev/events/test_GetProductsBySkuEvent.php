<?
require_once $_SERVER[ 'DOCUMENT_ROOT' ] . '/src/classes/queue/queue.php';
require_once $_SERVER[ 'DOCUMENT_ROOT' ] . '/src/classes/events_factory/TaskFactory.php';
require_once $_SERVER[ 'DOCUMENT_ROOT' ] . '/src/utils/custom_utils.php';
//edebug(true);
$queue = new MySQLQueue(new TaskFactory);

#Test case 1
$event_data = [
    'SKU'=>100,
];
$data = [
    'EVENT_TYPE'=>'get_product_by_sku',
    'PROJECT_ID'=>1,
    'WAREHOUSE_ID'=>1,
    'DATA'=>$event_data
];
dumpEx($data,"Test case 1. Get products by sku: {$event_data['SKU']}");

try
{
    $event_id = $queue->push($data)->data;
    dumpEx($event_id,"Event id");
    $queue->pop_event()->exec();
    $event_result_data = $queue->get_event_result($event_id);
    dumpEx($event_result_data->get_event_result_data(),"Test case 1. Event result data");
    
}
catch(Exception $e)
{
    dumpEx($e, "Test case 1");
}

#Test case 2
$event_data = [
    'SKU'=>'gdb1550',
];
$data = [
    'EVENT_TYPE'=>'get_product_by_sku',
    'PROJECT_ID'=>1,
    'WAREHOUSE_ID'=>1,
    'DATA'=>$event_data
];
dumpEx($data,"Test case 2. Get products by sku: {$event_data['SKU']}");

try
{
    $event_id = $queue->push($data)->data;
    dumpEx($event_id,"Event id");
    $queue->pop_event()->exec();
    $event_result_data = $queue->get_event_result($event_id);
    dumpEx($event_result_data->get_event_result_data(),"Test case 1. Event result data");
    
}
catch(Exception $e)
{
    dumpEx($e, "Test case 2");
}

#Test case 3
$event_data = [
    'SKU'=>'asdf',
];
$data = [
    'EVENT_TYPE'=>'get_product_by_sku',
    'PROJECT_ID'=>1,
    'WAREHOUSE_ID'=>1,
    'DATA'=>$event_data
];
dumpEx($data,"Test case 3. Get products by sku: {$event_data['SKU']}");

try
{
    $event_id = $queue->push($data)->data;
    dumpEx($event_id,"Event id");
    $queue->pop_event()->exec();
    $event_result_data = $queue->get_event_result($event_id);
    dumpEx($event_result_data->get_event_result_data(),"Test case 1. Event result data");
    
}
catch(Exception $e)
{
    dumpEx($e, "Test case 3");
}