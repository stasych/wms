<?
require_once $_SERVER['DOCUMENT_ROOT']."/src/classes/events/IEvent.php";
require_once $_SERVER['DOCUMENT_ROOT']."/src/classes/CCore/CCore.php";

abstract class BaseEvent implements IEvent
{
    /**
     * @var CCore
     */
    protected $core = false;
    /**
     * @var IQueue
     */
    protected $queue = null;
    /**
     * @var array
     */
    protected $event_data = false;
    protected $event_id = false;
    protected $event_data_version = false;
    
    public function __construct(IEventData $event_data)
    {
        $this->event_id = $event_data->get_event_id();
        $this->event_data = $event_data->get_event_data();
        $this->event_data_version = $event_data->get_event_version();
        $this->core = new CCore($event_data->get_event_warehouse(),$event_data->get_event_project());
    }
    
    public function set_queue(IQueue &$queue)
    {
        $this->queue = $queue;
    }
    
    public function get_event_id(){return $this->event_id;}
    public function get_event_data_version(){return $this->event_data_version;}
}