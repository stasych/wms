<?
require_once $_SERVER['DOCUMENT_ROOT']."/src/classes/events/IEventData.php";
require_once $_SERVER['DOCUMENT_ROOT']."/src/classes/queue/IQueue.php";
interface IFactory
{
    public function __construct();
    public function produce(IEventData $event_data) : IEvent;
    public function set_queue(IQueue &$queue);
}