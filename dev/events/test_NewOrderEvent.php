<?
require_once $_SERVER[ 'DOCUMENT_ROOT' ] . '/src/classes/queue/queue.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/src/classes/events_factory/OrderFactory.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/src/utils/custom_utils.php';
//edebug(true);
$queue = new MySQLQueue(new OrderFactory);

#Test case 0
$event_data = [
    'PRODUCTS'=>[
        ['PRODUCT_ID'=>10,'AMOUNT'=>1],
        ['PRODUCT_ID'=>10,'AMOUNT'=>1],
        ['PRODUCT_ID'=>9,'AMOUNT'=>2]
    ],
    'ORDER_ID'=>3000,
    'DEADLINE'=>strtotime(date('Y-m-d H:i:s', strtotime(' +1 day')))
];

$request_data = [
    'EVENT_TYPE'=>'create_new_order',
    'PROJECT_ID'=>1,
    'WAREHOUSE_ID'=>1,
    'DATA'=>$event_data
];
dumpEx($request_data,"Test case 0. Request");
try
{
    $queue->push($request_data);
    $queue->pop_event()->exec();
    dumpEx('Executed',"Test case 1. Executed");
}
catch(Exception $e)
{
    dumpEx($e, "Exception.Test case 1");
}

#Test case 1
$event_data = [
    'PRODUCTS'=>[
        ['PRODUCT_ID'=>10,'AMOUNT'=>1],
        ['PRODUCT_ID'=>9,'AMOUNT'=>2]
    ],
    'ORDER_ID'=>3000,
    'DEADLINE'=>strtotime(date('Y-m-d H:i:s', strtotime(' +1 day')))
];

$request_data = [
    'EVENT_TYPE'=>'create_new_order',
    'PROJECT_ID'=>1,
    'WAREHOUSE_ID'=>1,
    'DATA'=>$event_data
];
dumpEx($request_data,"Test case 1. Request");
try
{
    $queue->push($request_data);
    $queue->pop_event()->exec();
    dumpEx('Executed',"Test case 1. Executed");
}
catch(Exception $e)
{
    dumpEx($e, "Test case 1");
}

#Test case 2
$event_data = [
    'PRODUCTS'=>[
        ['PRODUCT_ID'=>10,'AMOUNT'=>1],
    ],
    'ORDER_ID'=>4000,
    'DEADLINE'=>strtotime(date('Y-m-d H:i:s', strtotime(' +1 day')))
];

$request_data = [
    'EVENT_TYPE'=>'create_new_order',
    'PROJECT_ID'=>1,
    'WAREHOUSE_ID'=>1,
    'DATA'=>$event_data
];
dumpEx($request_data,"Test case 2. Request");
try
{
    $queue->push($request_data);
    $queue->pop_event()->exec();
    dumpEx('Executed',"Test case 2. Executed");
}
catch(Exception $e)
{
    dumpEx($e, "Test case 2");
}

#Test case 3
$event_data = [
    'PRODUCTS'=>[
        ['PRODUCT_ID'=>8,'AMOUNT'=>1],
    ],
    'ORDER_ID'=>5000,
    'DEADLINE'=>strtotime(date('Y-m-d H:i:s', strtotime(' +1 day')))
];

$request_data = [
    'EVENT_TYPE'=>'create_new_order',
    'PROJECT_ID'=>1,
    'WAREHOUSE_ID'=>1,
    'DATA'=>$event_data
];
dumpEx($request_data,"Test case 3. Request");
try
{
    $queue->push($request_data);
    $queue->pop_event()->exec();
    dumpEx('Executed',"Test case 3. Executed");
}
catch(Exception $e)
{
    dumpEx($e, "Test case 3");
}

#Test case 4
$event_data = [
    'PRODUCTS'=>[
        ['PRODUCT_ID'=>10,'AMOUNT'=>4],
    ],
    'ORDER_ID'=>6000,
    'DEADLINE'=>strtotime(date('Y-m-d H:i:s', strtotime(' +1 day')))
];

$request_data = [
    'EVENT_TYPE'=>'create_new_order',
    'PROJECT_ID'=>1,
    'WAREHOUSE_ID'=>1,
    'DATA'=>$event_data
];
dumpEx($request_data,"Test case 4. Request");
try
{
    $queue->push($request_data);
    $queue->pop_event()->exec();
    dumpEx('Executed',"Test case 4. Executed");
}
catch(Exception $e)
{
    dumpEx($e, "Test case 4");
}