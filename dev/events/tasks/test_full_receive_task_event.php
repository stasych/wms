<?
require_once $_SERVER[ 'DOCUMENT_ROOT' ] . '/src/classes/queue/queue.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/src/classes/events_factory/TaskFactory.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/src/classes/events_factory/OrderFactory.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/src/utils/custom_utils.php';
edebug(true);
$queue = new MySQLQueue(new TaskFactory);
$orders_queue = new MySQLQueue(new OrderFactory);

$product_id1 = 2222;
$product_id2 = 2223;
$order_id = 102;
try
{
    #Add new order
    
    $new_order = [
        'EVENT_TYPE'=>'create_new_order',
        'PROJECT_ID'=>1,
        'WAREHOUSE_ID'=>1,
        'DATA'=>[
            'PRODUCTS'=>[
                ['PRODUCT_ID'=>$product_id1,'AMOUNT'=>1],
                ['PRODUCT_ID'=>$product_id2,'AMOUNT'=>1],
            ],
            'ORDER_ID'=>$order_id,
            'DEADLINE'=>strtotime(date('Y-m-d H:i:s', strtotime(' +1 day')))
        ]
    ];
    $event_id = $orders_queue->push($new_order)->data;
    $orders_queue->pop_event()->exec();
    $event_result_data = $orders_queue->get_event_result($event_id);
    dumpEx($event_result_data->get_event_result_data(),"Add new order_id:{$order_id}");
    
    #add new receive task
    $create_receive_task = [
        'EVENT_TYPE'=>'create_receive_task',
        'PROJECT_ID'=>1,
        'WAREHOUSE_ID'=>1,
        'DATA'=>[
            'USER_ID'=>1,
        ]
    ];
    dumpEx($create_receive_task,"Create new receive task");
    
    $event_id = $queue->push($create_receive_task)->data;
    $queue->pop_event()->exec();
    $event_result_data = $queue->get_event_result($event_id);
    dumpEx($event_result_data->get_event_result_data(),"Create new receive task result");
    $receive_task_id = $event_result_data->get_event_result_data()['NEW_TASK_ID'];
    
    #create new item
    $create_new_item1 = [
        'EVENT_TYPE'=>'create_new_item',
        'PROJECT_ID'=>1,
        'WAREHOUSE_ID'=>1,
        'DATA'=>[
            'TASK_ID'=>$receive_task_id,
            'PRODUCT_ID'=>$product_id1,
            'ADDRESS_ID'=>1,
            'BARCODE'=>'000111',
            'SKU'=>'SKU 2222',
            'BRAND'=>'BRAND2',
            'NAME'=>'PRODUCT 2222',
        ]
    ];
    $create_new_item2 = [
        'EVENT_TYPE'=>'create_new_item',
        'PROJECT_ID'=>1,
        'WAREHOUSE_ID'=>1,
        'DATA'=>[
            'TASK_ID'=>$receive_task_id,
            'PRODUCT_ID'=>$product_id2,
            'ADDRESS_ID'=>1,
            'BARCODE'=>'111000',
            'SKU'=>'SKU 2223',
            'BRAND'=>'BRAND2',
            'NAME'=>'PRODUCT 2223',
        ]
    ];
    $create_new_item3 = [
        'EVENT_TYPE'=>'create_new_item',
        'PROJECT_ID'=>1,
        'WAREHOUSE_ID'=>1,
        'DATA'=>[
            'TASK_ID'=>$receive_task_id,
            'PRODUCT_ID'=>123,
            'ADDRESS_ID'=>1,
            'BARCODE'=>'1100000',
            'SKU'=>'SKU 123',
            'BRAND'=>'BRAND123',
            'NAME'=>'PRODUCT 123',
        ]
    ];
    dumpEx($create_new_item1,"Create new item1");
    dumpEx($create_new_item2,"Create new item2");
    dumpEx($create_new_item3,"Create new item3");
    
    $event_id = $queue->push($create_new_item1)->data;
    $queue->pop_event()->exec();
    $event_result_data = $queue->get_event_result($event_id);
    dumpEx($event_result_data->get_event_result_data(),"Create new item1 result");
    $new_item_id1 = $event_result_data->get_event_result_data()['DATA'];
    
    $event_id = $queue->push($create_new_item2)->data;
    $queue->pop_event()->exec();
    $event_result_data = $queue->get_event_result($event_id);
    dumpEx($event_result_data->get_event_result_data(),"Create new item2 result");
    $new_item_id2 = $event_result_data->get_event_result_data()['DATA'];
    
    $event_id = $queue->push($create_new_item3)->data;
    $queue->pop_event()->exec();
    $event_result_data = $queue->get_event_result($event_id);
    dumpEx($event_result_data->get_event_result_data(),"Create new item3 result");
    $new_item_id3 = $event_result_data->get_event_result_data()['DATA'];
    
    #Complete receive task
    $complete_receive_task = [
        'EVENT_TYPE'=>'complete_receive_task',
        'PROJECT_ID'=>1,
        'WAREHOUSE_ID'=>1,
        'DATA'=>[
            'TASK_ID'=>$receive_task_id,
            'PRODUCTS'=>[
                ['ITEM_ID'=>$new_item_id1,'PRODUCT_ID'=>$product_id1,'ADDRESS_ID'=>100,'PRICE'=>22.22,'SUPPLIER_ID'=>1],
                ['ITEM_ID'=>$new_item_id2,'PRODUCT_ID'=>$product_id2,'ADDRESS_ID'=>100,'PRICE'=>22.23,'SUPPLIER_ID'=>1],
                ['ITEM_ID'=>$new_item_id3,'PRODUCT_ID'=>123,'ADDRESS_ID'=>100,'PRICE'=>123.32,'SUPPLIER_ID'=>1],
            ],
            'TN'=>[
                'TN_NUMBER'=>"TN NUMBER {$receive_task_id}",
                'INVOICE_NUMBER'=>"INVOICE NUMBER {$receive_task_id}",
                'TN_SUM'=>22.22+22.23+123.32,
                'TN_DATE'=>time(),
                'SUPPLIER_ID'=>1,
            ],
        ]
    ];
    dumpEx($complete_receive_task,"Complete receive_task_id: {$receive_task_id}");
    
    $event_id = $queue->push($complete_receive_task)->data;
    $queue->pop_event()->exec();
    $event_result_data = $queue->get_event_result($event_id);
    dumpEx($event_result_data->get_event_result_data(),"Complete receive task result");
    
}
catch(Exception $e)
{
    dumpEx($e, "Exception");
}

