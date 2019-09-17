<?
require_once $_SERVER['DOCUMENT_ROOT'].'/src/classes/events/BaseEvent.php';

class NewOrderEvent extends BaseEvent
{
    public function check_event_data()
    {
        if(!isset($this->event_data['PRODUCTS']))
            throw new CError('No PRODUCTS in event data',
                             EVENT_DATA_NEW_ORDER_NO_PRODUCT);
        if(!isset($this->event_data['ORDER_ID']))
            throw new CError('No ORDER_ID in event data',
                             EVENT_DATA_NEW_ORDER_NO_ORDER_ID);
        if(!isset($this->event_data['DEADLINE']))
            throw new CError('No DEADLINE in event data',
                             EVENT_DATA_NEW_ORDER_NO_ORDER_ID);
    
        if(!$this->validate_for_unique_id($this->event_data['PRODUCTS']))
            throw new CError('Not unique products ids in PRODUCTS in event data',
                             EVENT_DATA_EDIT_ORDER_NOT_UNIQUE_PRODUCTS);
    }
    
    public function exec()
    {
        $res = $this->core->prepare_products($this->get_products());
        $res = $this->core->add_new_order($res->data['PRODUCTS'],$this->get_order_id(),$res->data['OVERALL_STATUS'],$this->get_deadline());
        return $res;
    }
    
    private function get_products(){ return $this->event_data['PRODUCTS']; }
    
    private function get_products_ids()
    {
        return array_map( function ($val){ return $val[ 'PRODUCT_ID' ]; },
                            $this->get_products() );
    }
    
    private function get_deadline(){ return $this->event_data['DEADLINE']; }
    private function get_order_id(){ return $this->event_data['ORDER_ID']; }
    
    /**
     * Validate for unique products ids
     * @param $products_array
     * @return bool true if unique, false - otherwise
     */
    private function validate_for_unique_id(&$products_array)
    {
        $ids = [];
        foreach($products_array as $product)
        {
            if(in_array($product['PRODUCT_ID'],$ids))
                return false;
            $ids[] = $product['PRODUCT_ID'];
        }
        return true;
    }
}