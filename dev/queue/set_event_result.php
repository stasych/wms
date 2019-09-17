<?
require_once $_SERVER[ 'DOCUMENT_ROOT' ] . '/src/classes/queue/queue.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/src/utils/custom_utils.php';
//edebug(true);

$queue = new MySQLQueue;
$event_data = [
    'PRODUCTS'=>[
        ['PRODUCT_ID'=>1,'AMOUNT'=>10],
        ['PRODUCT_ID'=>2,'AMOUNT'=>20],
        ['PRODUCT_ID'=>3,'AMOUNT'=>30]
    ],
    'ORDER_ID'=>1
];
$test_event = [
    'EVENT_TYPE'=>'test_event',
    'PROJECT_ID'=>1,
    'WAREHOUSE_ID'=>1,
    'DATA'=>$event_data
];
try
{
    $queue->push($test_event);
    $event = $queue->pop();
    $event_result = [
        'PRODUCTS'=>[
            ['PRODUCT_ID'=>1,'RESULT'=>'Inserted'],
            ['PRODUCT_ID'=>2,'RESULT'=>'Updated'],
            ['PRODUCT_ID'=>3,'RESULT'=>'Canceled']
        ],
        'ORDER_ID'=>1
    ];
    dumpEx($event_result,'Event result to set');
    $queue->set_event_result($event->get_event_id(),$event_result);
    $event_result = $queue->get_event_result($event->get_event_id());
    dumpEx($event_result->get_event_data(),'Event data');
    dumpEx($event_result->get_event_result_data(),'Result event data');
}
catch(Exception $e)
{
    dumpEx($e);
}