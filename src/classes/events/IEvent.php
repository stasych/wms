<?
require_once $_SERVER['DOCUMENT_ROOT']."/src/classes/events/IEventData.php";
require_once $_SERVER['DOCUMENT_ROOT']."/src/classes/queue/IQueue.php";
interface IEvent
{
    public function __construct(IEventData $event_data);
    public function check_event_data();
    public function exec();
    public function set_queue(IQueue &$queue);
    public function get_event_id();
}