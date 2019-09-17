<?
require_once dirname(__DIR__)."/../utils/init_cli.php";
require_once $_SERVER[ 'DOCUMENT_ROOT' ] . '/src/classes/queue/print_queue.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/src/classes/events_factory/PrintFactory.php';

$print_queue = new MySQLPrintQueue( new PrintFactory );

while(true)
{
    try
    {
        $event = $print_queue->pop_event();
        $event_id = $event->get_event_id();
        $res = $event->exec();
    }
    catch(Exception $e)
    {
        if($e->getCode() !== EMPTY_QUEUE)
            $print_queue->set_event_result($event_id,
                                           ['CODE' => DEF_ERROR,
                                            'EXCEPTION_CODE' => $e->getCode(),
                                            'MSG' => $e->getMessage(),
                                            'FULL_ERROR' => "{$e}"
                                           ]);
    }
}