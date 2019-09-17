<?
require_once $_SERVER['DOCUMENT_ROOT'].'/src/classes/events/BaseEvent.php';

class CancelReceiveTaskEvent extends BaseEvent
{
    
    public function check_event_data()
    {
        if(!isset($this->event_data['TASK_ID']))
            throw new CError('No TASK_ID in event data',
                             EVENT_DATA_CANCEL_ORDER_TASK_NO_TASK_ID);
    }
    
    public function exec()
    {
        $this->core->cancel_receive_task($this->get_task_id());
    }
    
    private function get_task_id(){ return $this->event_data['TASK_ID']; }
    
}