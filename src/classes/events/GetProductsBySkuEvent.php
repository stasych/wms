<?
require_once $_SERVER['DOCUMENT_ROOT'].'/src/classes/events/BaseEvent.php';

class GetProductsBySkuEvent extends BaseEvent
{
    public function check_event_data()
    {
        if(!isset($this->event_data['SKU']))
            throw new CError('No SKU in event data',
                             EVENT_DATA_GET_PRODUCTS_BY_SKU_NO_SKU);
    
    }
    
    public function exec()
    {
        $res = $this->core->get_products_by_sku($this->get_sku());
        $event_result_data = [
            'CODE'=>$res->code,
            'MSG'=>$res->msg,
            'DATA'=>[]
        ];
        if(!$res->success())
        {
            $res = $this->core->api_get_products_by_sku($this->get_sku());
            $event_result_data['CODE'] = $res->code;
            $event_result_data['MSG'] = $res->msg;
            if($res->success())
                $event_result_data['DATA'] = $res->data['RESPONSE'];
        }
        else
            $event_result_data['DATA'] = $res->data;
            
        $this->queue->set_event_result($this->event_id,$event_result_data);
    }
    
    private function get_sku(){ return $this->event_data['SKU'];}
}