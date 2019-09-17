<?
require_once $_SERVER[ 'DOCUMENT_ROOT' ] . '/src/classes/queue/queue.php';
require_once $_SERVER[ 'DOCUMENT_ROOT' ] . '/src/classes/events_factory/TaskFactory.php';
require_once $_SERVER[ 'DOCUMENT_ROOT' ] . '/src/utils/custom_utils.php';
edebug(true);
$queue = new MySQLQueue(new TaskFactory);

#Test case 1
$event_data = [
    'TASK_ID'=>10,
    'PRODUCT_ID'=>1000,
    'ADDRESS_ID'=>1,
    'BARCODE'=>10101010,
    'BRAND'=>'TEST BRAND',
    'SKU'=>'TEST SKU',
    'NAME'=>'TEST PRODUCT',
];
$data = [
    'EVENT_TYPE'=>'create_new_item',
    'PROJECT_ID'=>1,
    'WAREHOUSE_ID'=>1,
    'DATA'=>$event_data
];
dumpEx($data,"Test case 1. Create new item");

try
{
    $event_id = $queue->push($data)->data;
    dumpEx($event_id,"Event id");
//    $queue->pop_event()->exec();
    $event_result_data = $queue->get_event_result($event_id);
    dumpEx($event_result_data->get_event_result_data(),"Test case 1. Event result data");
    
}
catch(Exception $e)
{
    dumpEx($e, "Test case 1");
}

#Test case 2
$event_data = [
    'TASK_ID'=>10,
    'PRODUCT_ID'=>10000,
    'ADDRESS_ID'=>1,
    'BARCODE'=>10101010,
    'SKU'=>'TEST SKU',
    'NAME'=>'TEST PRODUCT',
];
$data = [
    'EVENT_TYPE'=>'create_new_item',
    'PROJECT_ID'=>1,
    'WAREHOUSE_ID'=>1,
    'DATA'=>$event_data
];
dumpEx($data,"Test case 2. Create new item");

try
{
    $event_id = $queue->push($data)->data;
    dumpEx($event_id,"Event id");
//    $queue->pop_event()->exec();
    $event_result_data = $queue->get_event_result($event_id);
    dumpEx($event_result_data->get_event_result_data(),"Test case 2. Event result data");
    
}
catch(Exception $e)
{
    dumpEx($e, "Test case 2");
}

#Test case 3
$event_data = [
    'TASK_ID'=>10,
    'PRODUCT_ID'=>123456,
    'ADDRESS_ID'=>1,
    'BARCODE'=>123654,
    'BRAND'=>'TEST TRW',
    'SKU'=>'TEST GDB1550',
    'NAME'=>'Тестовые колодки тестовые задние',
];
$data = [
    'EVENT_TYPE'=>'create_new_item',
    'PROJECT_ID'=>1,
    'WAREHOUSE_ID'=>1,
    'DATA'=>$event_data
];
dumpEx($data,"Test case 3. Create new item");

try
{
    $event_id = $queue->push($data)->data;
    dumpEx($event_id,"Event id");
//    $queue->pop_event()->exec();
    $event_result_data = $queue->get_event_result($event_id);
    dumpEx($event_result_data->get_event_result_data(),"Test case 3. Event result data");
    
}
catch(Exception $e)
{
    dumpEx($e,
           "Test case 3");
}