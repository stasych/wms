<?
require_once $_SERVER['DOCUMENT_ROOT'].'/src/classes/events/BaseEvent.php';

class CancelOrderEvent extends BaseEvent
{
    public function check_event_data()
    {
        if(!isset($this->event_data['ORDER_ID']))
            throw new CError('No ORDER_ID in event data',
                             EVENT_DATA_CANCEL_ORDER_NO_ORDER_ID);
        
    }
    
    public function exec()
    {
        $this->core->cancel_order($this->get_order_id());
    }
    
    private function get_order_id(){ return $this->event_data['ORDER_ID']; }
}