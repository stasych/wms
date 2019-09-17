<?
require_once $_SERVER['DOCUMENT_ROOT'].'/src/classes/events/BaseEvent.php';

class CompleteReceiveTaskEvent extends BaseEvent
{
    public function exec()
    {
        $res = $this->core->create_tn($this->get_task_id(),$this->get_tn_number(),$this->get_invoice_number(),
                                        $this->get_tn_sum(),$this->get_tn_date(),$this->get_tn_supplier_id(),
                                        $this->get_tn_nds(),$this->get_tn_nds_sum(),$this->get_tn_img_id());
        $tn_id = $res->data['TN_ID'];
        $res = $this->core->get_items_by_receive_task($this->get_task_id());
        if(!$res->success())
            return $this->queue->set_event_result($this->event_id,['CODE'=>404,'MSG'=>$res->msg]);
        
        //compare items in db, that added to task, and items that receiving for completing task
        $products = $this->get_products();
        $items = $res->data;
        $items_ids_in_db = array_map(function($val){return intval($val['ID']);},$items);
        $items_ids_in_request = array_map(function($val){return intval($this->get_product_property($val,'ITEM_ID'));},
            $products);
        $diff_left = array_diff($items_ids_in_db,$items_ids_in_request);
        $diff_right = array_diff($items_ids_in_request,$items_ids_in_db);
        $diff = array_merge($diff_left,$diff_right);
        if($diff) //arrays values not equal
            return $this->queue->set_event_result($this->event_id,
                                                  ['CODE' => 404,
                                                   'MSG' => "Items added to DB, and items in request different",
                                                   'DATA' => ['DIFF_ITEMS' => $diff]
                                                  ]);
        //update_item
        foreach($products as $product)
        {
            $this->core->update_item($this->get_product_property($product,'ITEM_ID'),
                                     $this->get_product_property($product,'ADDRESS_ID'),
                                     'ON_WAREHOUSE',
                                     $tn_id,
                                     false,
                                     $this->get_product_property($product,'PRICE'),
                                     $this->get_product_property($product,'NDS'),
                                     $this->get_product_property($product,'NDS_SUM'),
                                     $this->get_product_property($product,'GTD'),
                                     $this->get_product_property($product,'COUNTRY_ID'),
                                     $this->get_tn_supplier_id());
        }
        // reserve products
        $reserved = [];
        $res = $this->core->get_orders_by_statuses(['PENDING_PRODUCTS']);
        if($res->success())
        {
            $orders_pending_products = $res->data;
            foreach($orders_pending_products as $pending_order )
            {
                if(!$products)
                    break;
                foreach($products as $k=>$product)
                {
                    $res =$this->core->reserve_product_for_order($pending_order['ID'],$this->get_product_property($product,'PRODUCT_ID'));
                    if($res->success())
                    {
                        $reserved[] = ['PRODUCT_ID'=>$this->get_product_property($product,'PRODUCT_ID'),
                                       'ITEM_ID'=>$this->get_product_property($product,'ITEM_ID')];
                        unset($products[$k]);
                    }
                }
            }
        }
        //update receive_task status
        $this->core->complete_receive_task($this->get_task_id(),'SUCCESS');
        $this->queue->set_event_result($this->event_id,
                                       ['CODE' => 200,
                                        'MSG' => "Task with ID: {$this->get_task_id()} successfully complete",
                                        'DATA'=>['RESERVED'=>$reserved]
                                       ]);
    }
    
    public function check_event_data()
    {
        if(!isset($this->event_data['TASK_ID']))
            throw new CError('No TASK_ID in event data',
                             EVENT_DATA_COMPLETE_RECEIVE_TASK_NO_TASK_ID);
//        if(!isset($this->event_data['AMOUNT']))  //???
//            throw new CError('No AMOUNT in event data',EVENT_DATA_COMPLETE_RECEIVE_TASK_NO_AMOUNT,__FUNCTION__);
        if(!isset($this->event_data['PRODUCTS']))
            throw new CError('No PRODUCTS in event data',
                             EVENT_DATA_COMPLETE_RECEIVE_TASK_NO_PRODUCTS);
        if(!isset($this->event_data['TN']))
            throw new CError('No TN in event data',
                             EVENT_DATA_COMPLETE_RECEIVE_TASK_NO_TN);
        if(!isset($this->event_data['TN']['TN_NUMBER']))
            throw new CError('No TN_NUMBER in event data',
                             EVENT_DATA_COMPLETE_RECEIVE_TASK_NO_TN_NUMBER);
        if(!isset($this->event_data['TN']['INVOICE_NUMBER']))
            throw new CError('No INVOICE_NUMBER in event data',
                             EVENT_DATA_COMPLETE_RECEIVE_TASK_NO_INVOICE_NUMBER);
        if(!isset($this->event_data['TN']['TN_SUM']))
            throw new CError('No TN_SUM in event data',
                             EVENT_DATA_COMPLETE_RECEIVE_TASK_NO_TN_SUM);
        if(!isset($this->event_data['TN']['TN_DATE']))
            throw new CError('No TN_DATE in event data',
                             EVENT_DATA_COMPLETE_RECEIVE_TASK_NO_TN_DATE);
        if(!isset($this->event_data['TN']['SUPPLIER_ID']))
            throw new CError('No SUPPLIER_ID in event data',
                             EVENT_DATA_COMPLETE_RECEIVE_TASK_NO_SUPPLIER_ID);
    
    
        if(!$this->validate_for_unique_id($this->event_data['PRODUCTS']))
            throw new CError('Not unique items ids in PRODUCTS in event data',
                             EVENT_DATA_COMPLETE_RECEIVE_TASK_NOT_UNIQUE_ITEMS);
    }
    
    private function get_products(){return $this->event_data['PRODUCTS'];}
    private function get_task_id(){return $this->event_data['TASK_ID'];}
    private function get_tn(){return $this->event_data['TN'];}
    private function get_tn_number(){return $this->event_data['TN']['TN_NUMBER'];}
    private function get_tn_supplier_id(){return $this->event_data['TN']['SUPPLIER_ID'];}
    private function get_invoice_number(){return $this->event_data['TN']['INVOICE_NUMBER'];}
    private function get_tn_sum(){return $this->event_data['TN']['TN_SUM'];}
    private function get_tn_date(){return $this->event_data['TN']['TN_DATE'];}
    private function get_tn_img_id(){return isset($this->event_data['TN']['IMG_ID'])? $this->event_data['TN']['IMG_ID']:false;}
    private function get_tn_nds(){return isset($this->event_data['TN']['TN_NDS']) ? $this->event_data['TN']['TN_NDS']:false;}
    private function get_tn_nds_sum(){return isset($this->event_data['TN']['TN_NDS_SUM']) ? $this->event_data['TN']['TN_NDS_SUM']:false;}
    
    private function get_product_property(&$product,$key)
    {
        $optional_keys = ['NDS','NDS_SUM','GTD','COUNTRY_ID'];
        if(in_array($key,$optional_keys))
            return isset($product[$key]) ? $product[$key] : false;
        if(!isset($product[$key]))
            throw new CError("No {$key} in event data",
                             EVENT_DATA_COMPLETE_RECEIVE_TASK_NO_KEY_IN_PRODUCT);
        return $product[$key];
    }
    
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
            if(in_array($product['ITEM_ID'],$ids))
                return false;
            $ids[] = $product['ITEM_ID'];
        }
        return true;
    }
    
}