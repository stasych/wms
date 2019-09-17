<?
require_once $_SERVER['DOCUMENT_ROOT'].'/src/classes/events/BaseEvent.php';

class PrintItemBarcodeEvent extends BaseEvent
{
    private $test_dir = '/dev/shm/print_test';
    public function check_event_data()
    {
        if(!isset($this->event_data['ITEM_ID']))
            throw new CError('No ITEM_ID in event data',
                             EVENT_DATA_CANCEL_ORDER_NO_ORDER_ID);
    }
    
    public function exec()
    {
        if(!file_exists($this->test_dir))
            mkdir($this->test_dir);
        $file_name = tempnam($this->test_dir,'PR-');
        file_put_contents($file_name,$this->get_item_id(),FILE_APPEND);
    
        return $this->queue->set_event_result($this->event_id,
                                              ['CODE' => 200,
                                               'MSG' => "Barcode printed for item_id: {$this->get_item_id()} created",
                                               'DATA' => ['FILE_NAME' => $file_name]
                                              ]);
    }
    
    private function get_item_id(){ return $this->event_data['ITEM_ID'];}
    
}