<?
require_once '../../src/utils/init_cli.php';
require_once $_SERVER[ 'DOCUMENT_ROOT' ] . '/src/classes/queue/queue.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/src/utils/custom_utils.php';
//edebug(true);

//function fatal_handler()
//{
//    echo "Fatal".PHP_EOL;
//}
//register_shutdown_function('fatal_handler');

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
$event_result = [
    'PRODUCTS'=>[
        ['PRODUCT_ID'=>1,'RESULT'=>'Inserted'],
        ['PRODUCT_ID'=>2,'RESULT'=>'Updated'],
        ['PRODUCT_ID'=>3,'RESULT'=>'Canceled']
    ],
    'ORDER_ID'=>1
];
try
{
    $event_id = $queue->push($test_event)->data;
    $queue->set_event_result($event_id,$event_result);
    $event_result = $queue->get_event_result($event_id,10);
    var_dump($event_result);
}
catch(Exception $e)
{
    dumpEx($e);
}