<?
require_once $_SERVER['DOCUMENT_ROOT'].'/src/classes/events/BaseEvent.php';

class CreateReceiveTaskEvent extends BaseEvent
{
    public function check_event_data()
    {
        if(!isset($this->event_data['USER_ID']))
            throw new CError('No USER_ID in event data',
                             EVENT_DATA_CREATE_ORDER_TASK_NO_USER_ID);
    }
    
    public function exec()
    {
        $task_id = $this->core->create_new_receive_task($this->get_user_id(),$this->get_date_receive())->data;
        $this->queue->set_event_result($this->event_id,['NEW_TASK_ID'=>$task_id]);
    }
    
    private function get_user_id(){ return $this->event_data['USER_ID']; }
    private function get_date_receive(){ return isset($this->event_data['DATE_RECEIVE'])? $this->event_data['DATE_RECEIVE'] : false; }
    
}