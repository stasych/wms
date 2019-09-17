<?
require_once $_SERVER[ 'DOCUMENT_ROOT' ] . '/src/classes/queue/queue.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/src/classes/events_factory/OrderFactory.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/src/utils/custom_utils.php';
//edebug(true);
$queue = new MySQLQueue(new OrderFactory);

#Test case 1
//$event_data = [
//    'PRODUCTS'=>[
//        ['PRODUCT_ID'=>10,'AMOUNT'=>1],
//        ['PRODUCT_ID'=>9,'AMOUNT'=>2]
//    ],
//    'ORDER_ID'=>3000,
//    'DEADLINE'=>strtotime(date('Y-m-d H:i:s', strtotime(' +1 day')))
//];
//$create_data = [
//    'EVENT_TYPE'=>'create_new_order',
//    'PROJECT_ID'=>1,
//    'WAREHOUSE_ID'=>1,
//    'DATA'=>$event_data
//];
//dumpEx($create_data,"Test case 1. Create data");

$event_data = [
    'PRODUCTS'=>[
//        ['PRODUCT_ID'=>10,'AMOUNT'=>3],
        ['PRODUCT_ID'=>9,'AMOUNT'=>2]
    ],
    'ORDER_ID'=>3,
    'DEADLINE'=>strtotime(date('Y-m-d H:i:s', strtotime(' +1 day')))
];
$edit_data = [
    'EVENT_TYPE'=>'edit_order',
    'PROJECT_ID'=>1,
    'WAREHOUSE_ID'=>1,
    'DATA'=>$event_data
];
dumpEx($edit_data,"Test case 1. Edit data");
try
{
//    $queue->push($create_data);
    $queue->push($edit_data);
//    $queue->pop_event()->exec();
    $queue->pop_event()->exec();
    dumpEx('Executed',"Test case 1. Executed");
}
catch(Exception $e)
{
    dumpEx($e, "Test case 1");
}