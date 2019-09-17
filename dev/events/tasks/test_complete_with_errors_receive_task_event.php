<?
require_once $_SERVER[ 'DOCUMENT_ROOT' ] . '/src/classes/queue/queue.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/src/classes/events_factory/TaskFactory.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/src/utils/custom_utils.php';
edebug(true);
$queue = new MySQLQueue(new TaskFactory);

$product_id1 = 2224;
$product_id2 = 2225;
$receive_task_id = false;
try
{
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
            'BARCODE'=>"00{$product_id1}",
            'SKU'=>"SKU {$product_id1}",
            'BRAND'=>'BRAND2',
            'NAME'=>"PRODUCT {$product_id1}",
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
            'BARCODE'=>"00{$product_id2}",
            'SKU'=>"SKU {$product_id2}",
            'BRAND'=>'BRAND2',
            'NAME'=>"PRODUCT {$product_id2}",
        ]
    ];
    dumpEx($create_new_item1,"Create new item1");
    dumpEx($create_new_item2,"Create new item2");
    
    $event_id = $queue->push($create_new_item1)->data;
    $queue->pop_event()->exec();
    $event_result_data = $queue->get_event_result($event_id);
    dumpEx($event_result_data->get_event_result_data(),"Create new item1 result");
    if($event_result_data->get_event_result_data()['CODE']==200)
        $new_item_id1 = $event_result_data->get_event_result_data()['DATA'];
    else
    {
        $new_item_id1 = false;
        dumpEx($event_result_data->get_event_result_data()['MSG'],'Error event result');
    }
    
    
    $event_id = $queue->push($create_new_item2)->data;
    $queue->pop_event()->exec();
    $event_result_data = $queue->get_event_result($event_id);
    dumpEx($event_result_data->get_event_result_data(),"Create new item2 result");
    if($event_result_data->get_event_result_data()['CODE']==200)
        $new_item_id2 = $event_result_data->get_event_result_data()['DATA'];
    else
    {
        $new_item_id2 = false;
        dumpEx($event_result_data->get_event_result_data()['MSG'],'Error event result');
    }
    
}
catch(Exception $e)
{
    dumpEx($e, "Exception");
}

# try complete wrong way
try
{
    #Complete receive task
    $complete_receive_task = [
        'EVENT_TYPE'=>'complete_receive_task',
        'PROJECT_ID'=>1,
        'WAREHOUSE_ID'=>1,
        'DATA'=>[
            'TASK_ID'=>$receive_task_id,
            'PRODUCTS'=>[
                ['ITEM_ID'=>$new_item_id1,'PRODUCT_ID'=>$product_id1,'ADDRESS_ID'=>100,'PRICE'=>32.22],
                ['ITEM_ID'=>$new_item_id2,'PRODUCT_ID'=>$product_id2,'ADDRESS_ID'=>100,'PRICE'=>32.23],
                ['ITEM_ID'=>11111,'PRODUCT_ID'=>123,'ADDRESS_ID'=>100,'PRICE'=>200.01],
            ],
            'TN'=>[
                'TN_NUMBER'=>"TN NUMBER {$receive_task_id}",
                'INVOICE_NUMBER'=>"INVOICE NUMBER {$receive_task_id}",
                'TN_SUM'=>32.22+32.23+200.01,
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

# try complete right way
try
{
    #Complete receive task
    $complete_receive_task = [
        'EVENT_TYPE'=>'complete_receive_task',
        'PROJECT_ID'=>1,
        'WAREHOUSE_ID'=>1,
        'DATA'=>[
            'TASK_ID'=>$receive_task_id,
            'PRODUCTS'=>[
                ['ITEM_ID'=>$new_item_id1,'PRODUCT_ID'=>$product_id1,'ADDRESS_ID'=>100,'PRICE'=>32.22],
                ['ITEM_ID'=>$new_item_id2,'PRODUCT_ID'=>$product_id2,'ADDRESS_ID'=>100,'PRICE'=>32.23],
            ],
            'TN'=>[
                'TN_NUMBER'=>"TN NUMBER UPDATED {$receive_task_id}",
                'INVOICE_NUMBER'=>"INVOICE NUMBER UPDATED {$receive_task_id}",
                'TN_SUM'=>32.22+32.23,
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

