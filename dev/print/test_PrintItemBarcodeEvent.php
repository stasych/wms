<?
require_once $_SERVER[ 'DOCUMENT_ROOT' ] . '/src/utils/custom_utils.php';
edebug(true);
require_once $_SERVER[ 'DOCUMENT_ROOT' ] . '/src/classes/queue/print_queue.php';
require_once $_SERVER[ 'DOCUMENT_ROOT' ] . '/src/classes/events_factory/PrintFactory.php';
$queue = new MySQLPrintQueue(new PrintFactory);

#Test case 1
$data = [
    'EVENT_TYPE'=>'print_item_barcode',
    'PROJECT_ID'=>1,
    'WAREHOUSE_ID'=>1,
    'DATA'=>[
        'ITEM_ID'=>123,
    ]
];
dumpEx($data,"Test case 1. Get info for order_id: {$data['DATA']['ITEM_ID']}");

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