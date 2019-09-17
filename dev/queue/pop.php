<?
require_once $_SERVER[ 'DOCUMENT_ROOT' ] . '/src/classes/queue/queue.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/src/classes/events_factory/OrderFactory.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/src/utils/custom_utils.php';
//edebug(true);
$queue = new MySQLQueue(new OrderFactory);
$n=1;
while($n--)
{
    try
    {
        $event_data = $queue->pop()->get_event_data();
        dumpEx($event_data, "Iteration {$n}");
    }
    catch(Exception $e)
    {
        dumpEx($e,"Iteration {$n}");
    }
    
}