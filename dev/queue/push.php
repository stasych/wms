<?
require_once $_SERVER[ 'DOCUMENT_ROOT' ] . '/src/classes/queue/queue.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/src/utils/custom_utils.php';

$queue = new MySQLQueue;
$n = 10;
while($n--)
{
    $response = [
        'EVENT_TYPE'=>'create_new_order',
        'PROJECT_ID'=>1,
        'WAREHOUSE_ID'=>1,
        'DATA'=>[
            'PRODUCTS'=>[
                ['PRODUCT_ID'=>1,'AMOUNT'=>10],
                ['PRODUCT_ID'=>2,'AMOUNT'=>20],
                ['PRODUCT_ID'=>3,'AMOUNT'=>30]
            ],
            'ORDER_ID'=>1
        ]
    ];
    try
    {
        $queue->push($response);
    }
    catch(Exception $e)
    {
        dumpEx($e);
    }
}