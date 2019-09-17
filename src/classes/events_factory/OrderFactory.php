<?
require_once $_SERVER['DOCUMENT_ROOT'].'/src/classes/events_factory/IFactory.php';
require_once $_SERVER[ 'DOCUMENT_ROOT' ] . '/src/classes/events/NewOrderEvent.php';
require_once $_SERVER[ 'DOCUMENT_ROOT' ] . '/src/classes/events/EditOrderEvent.php';
require_once $_SERVER[ 'DOCUMENT_ROOT' ] . '/src/classes/events/CancelOrderEvent.php';
require_once $_SERVER[ 'DOCUMENT_ROOT' ] . '/src/classes/events/GetOrderInfoEvent.php';

class OrderFactory implements IFactory
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
        if($class == 'create_new_order')
        {
            return new NewOrderEvent($event_data);
        }
        elseif($class == 'edit_order')
        {
            return new EditOrderEvent($event_data);
        }
        elseif($class == 'cancel_order')
        {
            return new CancelOrderEvent($event_data);
        }
        elseif($class == 'get_order_info')
        {
            return new GetOrderInfoEvent($event_data);
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