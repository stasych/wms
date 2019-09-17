<?
require_once dirname(__DIR__)."/../utils/init_cli.php";
require_once $_SERVER[ 'DOCUMENT_ROOT' ] . '/src/classes/queue/queue.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/src/classes/events_factory/TaskFactory.php';

$mysql_queue = new MySQLQueue( new TaskFactory );

while(true)
{
    try
    {
        $event = $mysql_queue->pop_event();
        $event_id = $event->get_event_id();
        $res = $event->exec();
    }
    catch(Exception $e)
    {
        if($e->getCode() !== EMPTY_QUEUE)
            $mysql_queue->set_event_result($event_id,
                                           ['CODE' => DEF_ERROR,
                                            'EXCEPTION_CODE' => $e->getCode(),
                                            'MSG' => $e->getMessage(),
                                            'FULL_ERROR' => "{$e}"
                                           ]);
    }
}