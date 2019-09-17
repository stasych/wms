<?
require_once $_SERVER['DOCUMENT_ROOT'].'/src/classes/events_factory/IFactory.php';
require_once $_SERVER[ 'DOCUMENT_ROOT' ] . '/src/classes/events/CreateReceiveTaskEvent.php';
require_once $_SERVER[ 'DOCUMENT_ROOT' ] . '/src/classes/events/CancelReceiveTaskEvent.php';
require_once $_SERVER[ 'DOCUMENT_ROOT' ] . '/src/classes/events/GetProductsBySkuEvent.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/src/classes/events/CreateNewItemEvent.php';
require_once $_SERVER[ 'DOCUMENT_ROOT' ] . '/src/classes/events/CompleteReceiveTaskEvent.php';

class TaskFactory implements IFactory
{
    private $queue = null;
    
    public function __construct()
    {
    
    }
    
    public function set_queue(IQueue &$queue)
    {
        $this->queue = $queue;
    }
    
    private function create_event($class, $event_data) : IEvent
    {
        if($class == 'create_receive_task')
        {
            return new CreateReceiveTaskEvent($event_data);
        }
        elseif($class == 'cancel_receive_task')
        {
            return new CancelReceiveTaskEvent($event_data);
        }
        elseif($class == 'get_product_by_sku')
        {
            return new GetProductsBySkuEvent($event_data);
        }
        elseif($class == 'create_new_item')
        {
            return new CreateNewItemEvent($event_data);
        }
        elseif($class == 'complete_receive_task')
        {
            return new CompleteReceiveTaskEvent($event_data);
        }
        else
        {
            throw new CError("Undefined event type: {$class}",
                             UNDEFINED_EVENT_TYPE);
        }
    }
    
    public function produce(IEventData $event_data) : IEvent
    {
        $class = $event_data->get_event_class();
        $event = $this->create_event($class, $event_data);
        $event->check_event_data();
        if(!is_null($this->queue))
            $event->set_queue($this->queue);
        return $event;
    }
}