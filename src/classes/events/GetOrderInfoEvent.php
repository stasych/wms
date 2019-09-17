<?
require_once $_SERVER['DOCUMENT_ROOT'].'/src/classes/events/BaseEvent.php';

class GetOrderInfoEvent extends BaseEvent
{
    public function check_event_data()
    {
        if(!isset($this->event_data['ORDER_ID']))
            throw new CError('No ORDER_ID in event data',
                             EVENT_DATA_GET_ORDER_INFO_NO_ORDER_ID);
    
    }
    
    public function exec()
    {
        $res = $this->core->get_order_info($this->get_order_id());
        $event_result_data = [
            'CODE'=>$res->code,
            'MSG'=>$res->msg,
            'DATA'=>$res->data
        ];
        $this->queue->set_event_result($this->event_id,$event_result_data);
    }
    
    private function get_order_id(){ return $this->event_data['ORDER_ID'];}
}