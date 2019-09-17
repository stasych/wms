<?
require_once $_SERVER['DOCUMENT_ROOT'].'/src/classes/events_factory/IFactory.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/src/classes/events/print/PrintItemBarcodeEvent.php';

class PrintFactory implements IFactory
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
        if($class == 'print_item_barcode')
        {
            return new PrintItemBarcodeEvent($event_data);
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