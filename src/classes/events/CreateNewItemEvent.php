<?
require_once $_SERVER['DOCUMENT_ROOT'].'/src/classes/events/BaseEvent.php';

require_once $_SERVER['DOCUMENT_ROOT'].'/src/classes/queue/print_queue.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/src/classes/events_factory/PrintFactory.php';

class CreateNewItemEvent extends BaseEvent
{
    public function check_event_data()
    {
        if(!isset($this->event_data['TASK_ID']))
            throw new CError('No TASK_ID in event data',
                             EVENT_DATA_CREATE_NEW_ITEM_NO_TASK_ID);
        if(!isset($this->event_data['PRODUCT_ID']))
            throw new CError('No PRODUCT_ID in event data',
                             EVENT_DATA_CREATE_NEW_ITEM_NO_PRODUCT_ID);
        if(!isset($this->event_data['ADDRESS_ID']))
            throw new CError('No ADDRESS_ID in event data',
                             EVENT_DATA_CREATE_NEW_ITEM_NO_ADDRESS_ID);
    
    }
    
    public function exec()
    {
        $res = $this->core->get_product_by_id($this->get_product_id());
        if($res->success())
        {
            if($this->get_barcode())
                if(is_null($res->data['BARCODE']) || !$res->data['BARCODE'])
                    $this->core->update_product($this->get_product_id(),$this->get_barcode());
        }
        else
        {
            if(!$this->get_brand())
                return $this->queue->set_event_result($this->event_id,['CODE'=>400,'MSG'=>'Cant create product, no brand']);
            if(!$this->get_sku())
                return $this->queue->set_event_result($this->event_id,['CODE'=>400,'MSG'=>'Cant create product, no sku']);
            if(!$this->get_name())
                return $this->queue->set_event_result($this->event_id,['CODE'=>400,'MSG'=>'Cant create product, no name']);
            
            $this->core->create_new_product($this->get_product_id(),$this->get_brand(),$this->get_sku(),
                                            $this->get_name(),$this->get_barcode());
        }
        $new_item_id = $this->core->create_new_item( $this->get_task_id(),
                                      $this->get_product_id(),
                                      $this->get_price(),
                                      $this->get_nds(),
                                      $this->get_nds_sum(),
                                      $this->get_address_id(),
                                      $this->get_gtd(),
                                      $this->get_country_id(),
                                      false )->data;
        
        $print_queue = new MySQLPrintQueue(new PrintFactory);
        $print_queue->push(['EVENT_TYPE' => 'print_item_barcode',
                            'PROJECT_ID' => $this->core->get_project_id(),
                            'WAREHOUSE_ID' => $this->core->get_warehouse_id(),
                            'DATA' => [
                                'ITEM_ID' => $new_item_id,
                            ]
                           ]);
        return $this->queue->set_event_result($this->event_id,['CODE'=>200,'MSG'=>"New item with id: {$new_item_id} created",'DATA'=>$new_item_id]);
        
    }
    
    private function get_task_id(){return $this->event_data['TASK_ID'];}
    private function get_product_id(){return $this->event_data['PRODUCT_ID'];}
    private function get_address_id(){return $this->event_data['ADDRESS_ID'];}
    private function get_barcode(){return isset($this->event_data['BARCODE'])?$this->event_data['BARCODE']:false;}
    
    private function get_brand(){return isset($this->event_data['BRAND'])?$this->event_data['BRAND']:false;}
    private function get_sku(){return isset($this->event_data['SKU'])?$this->event_data['SKU']:false;}
    private function get_name(){return isset($this->event_data['NAME'])?$this->event_data['NAME']:false;}
    
    private function get_price(){return isset($this->event_data['PRICE'])?$this->event_data['PRICE']:false;}
    private function get_nds(){return isset($this->event_data['NDS'])?$this->event_data['NDS']:false;}
    private function get_nds_sum(){return isset($this->event_data['NDS_SUM'])?$this->event_data['NDS_SUM']:false;}
    private function get_gtd(){return isset($this->event_data['GTD'])?$this->event_data['GTD']:false;}
    private function get_country_id(){return isset($this->event_data['COUNTRY_ID'])?$this->event_data['COUNTRY_ID']:false;}
    
    
}