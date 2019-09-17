<?
require_once $_SERVER[ 'DOCUMENT_ROOT' ] . "/src/classes/CCore/globals.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/src/classes/CError/CError.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/src/classes/CRes/CRes.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/src/classes/CStatuses/CStatuses.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/src/classes/CSettings/CSettings.php";

class CCore
{
    public $db = false;
    public $statuses = false;
    public $settings = false;
    private $warehouse_id = false;
    private $project_id   = false;
    
    public function get_project_id(){return $this->project_id;}
    public function get_warehouse_id(){return $this->warehouse_id;}
    
    public static $t_orders         = 'orders';
    public static $t_order_products = 'order_products';
    public static $t_items          = 'items';
    public static $t_products       = 'products';
    public static $t_receive_tasks  = 'receive_tasks';
    public static $t_tns            = 'tns';
    
    public function t_orders(){ return self::$t_orders; }
    public function t_order_products(){ return self::$t_order_products; }
    public function t_items(){ return self::$t_items; }
    public function t_products(){ return self::$t_products; }
    public function t_receive_tasks(){ return self::$t_receive_tasks; }
    public function t_tns(){ return self::$t_tns; }
    
    public function s_default_brand(){ return $this->settings->get('DEFAULT_BRAND')?:'NONAME';}
    public function s_inner_api_point(){ return $this->settings->get('INNER_API_POINT')?:"http://dev.forsto.ru/ajax/wms/index.php";}
    public function s_inner_api_token(){ return $this->settings->get('INNER_API_TOKEN')?:"WMS_UNIQ_TOKEN_1";}
    public function s_default_receive_address(){ return $this->settings->get('DEFAULT_RECEIVE_ADDRESS')?:1;}
    public function s_default_nds(){ return $this->settings->get('DEFAULT_NDS')?:0.18;}
    
    public function __construct($warehouse_id,$project_id)
    {
        global $DB;
        $this->db = & $DB;
    
        if(!is_numeric($warehouse_id))
            throw new CError('warehouse_id not numeric',
                             WAREHOUSE_ID_NOT_NUMERIC);
        if(!is_numeric($project_id))
            throw new CError('project_id not numeric',
                             PROJECT_ID_NOT_NUMERIC);
        $this->warehouse_id = $warehouse_id;
        $this->project_id = $project_id;
        
        $this->statuses = new CStatuses($project_id);
        $this->settings = new CSettings($project_id,$warehouse_id);
    }
    
    public function set_context($context)
    {
        foreach($context as $k=>$v)
        {
            if(property_exists($this,$k))
                $this[$k] = $v;
        }
    }
    
    /**
     * Create new order products
     * @param $products array
     * @param $order_id
     * @param $order_status
     * @param $deadline
     * @return CRes
     * @throws CError
     */
    public function add_new_order($products,$order_id,$order_status,$deadline)
    {
        if(!isset($products))
            throw new CError('Empty func arg : empty products',
                             EMPTY_FUNCTION_ARG);
        if(!$products)
            throw new CError('Empty products',
                             EMPTY_PRODUCTS);
        if(!isset($order_id))
            throw new CError("Empty func arg : order_id",
                             EMPTY_FUNCTION_ARG);
        if(!is_numeric($order_id))
            throw new CError('order_id not numeric',
                             ORDER_ID_NOT_NUMERIC);
        if(!isset($order_status))
            throw new CError("Empty func arg : order_status",
                             EMPTY_FUNCTION_ARG);
        if(!isset($deadline))
            throw new CError("Empty func arg : deadline",
                             EMPTY_FUNCTION_ARG);
        
        
        //Insert returns only autoincrement
        $q_order_exists = "SELECT `ID` FROM {$this->t_orders()} WHERE `ID`={$order_id}
AND `STATUS_ID`<>{$this->statuses->get('ORDER','CANCELED')->id}
AND `WAREHOUSE_ID`={$this->warehouse_id} AND `PROJECT_ID`={$this->project_id};";
        if($this->db->CQuery($q_order_exists)->rows() > 0)
            throw new CError("Order:{$order_id} not created",
                             MYSQL_INSERT_ERROR);
        
        $this->db->InsertEx( $this->t_orders(),
                                    [
                                        'ID' => $order_id,
                                        'PROJECT_ID' => $this->project_id,
                                        'WAREHOUSE_ID' => $this->warehouse_id,
                                        'STATUS_ID' => $this->statuses->get('ORDER',$order_status)->id,
                                        'DEADLINE' => "FROM_UNIXTIME({$deadline})",
                                    ] );
        if($this->db->query_error())
            throw new CError("Order:{$order_id} not created",
                             MYSQL_INSERT_ERROR);
        
        $this->insert_products_for_order($products,$order_id);
        
        return new CRes( SUCCESS, "New order created ID:{$order_id}" );
    }
    
    /**
     * Check products for availability
     * @param $products array products ids to check on wh for free status
     * @throws CError
     * @return CRes return available amount for each product
     */
    function products_availability($products)
    {
        if(!$products)
            throw new CError('Empty products',
                             EMPTY_PRODUCTS);
        
        //first check all products on items in warehouse
        $in_products = implode(',', $products );
        $q = "SELECT `ID`,`PRODUCT_ID` FROM {$this->t_items()} WHERE `PRODUCT_ID` in ({$in_products})
AND `WAREHOUSE_ID`={$this->warehouse_id}
AND `STATUS_ID`={$this->statuses->get('ITEM','ON_WAREHOUSE')->id};";
        $rows = $this->db->CQuery($q)->FetchByKey();
        $items = [];
        
        if($rows)
        {
            foreach($rows as $row)
                $items[$row['PRODUCT_ID']] = isset($items[$row['PRODUCT_ID']]) ? $items[$row['PRODUCT_ID']]+1  : 1;
        }
        
        $diff = array_diff($products,array_keys($items));
        if($diff)
        {
            // has products not in items
//            $diff_str = implode( ',', $diff);
//            return new CRes(DEF_ERROR, "No products on warehouse : {$diff_str}");
            foreach($diff as $product_no_on_wh)
                $items[$product_no_on_wh] = 0;
        }
        
        //check reserved products
        $q = "SELECT `PRODUCT_ID` FROM {$this->t_order_products()} WHERE `PRODUCT_ID` in ({$in_products})
AND `STATUS_ID`={$this->statuses->get('ORDER_PRODUCTS','RESERVED')->id} AND `WAREHOUSE_ID`={$this->warehouse_id};";
        $rows = $this->db->CQuery($q)->FetchByKey();
        
        foreach($rows as $row)
        {
            $items[$row['PRODUCT_ID']]--;
//            if($items[$row['PRODUCT_ID']] < 1)
//                return new CRes(DEF_ERROR, "No available products for : {$row['PRODUCT_ID']}");
        }
        //all products have amount > 1 (available), and pending otherwise
        return new CRes(SUCCESS, "All products info", $items);
    }
    
    /**
     * Prepare products for insert/update operations.
     * Split products by availability status PENDING,RESERVE
     * @param $products
     * @throws CError
     * @return CRes PRODUCTS - products with statuses, OVERALL_STATUS - PENDING_COMPLECTATION, PENDING_PRODUCTS
     */
    public function prepare_products($products)
    {
        $products_ids = array_map( function ($val){ return $val[ 'PRODUCT_ID' ]; },$products);
        $res = $this->products_availability( $products_ids );
        if(!$res->success())
            throw new CError('Cant check products availability',
                             CANT_CHECK_PRODUCTS_AVAILABILITY);
        //set status for products for order and detect order status
        $overall_status = 'PENDING_COMPLECTATION';
        $products_with_statuses = [];
        foreach($products as $product)
        {
            $product_id = $product['PRODUCT_ID'];
            $amount = $res->data[$product_id];
            if($amount > 0) // has available products
            {
                if($amount >= $product['AMOUNT']) // enough available products
                {
                    $product['STATUS'] = 'RESERVED';
                    $products_with_statuses[] = $product;
                }
                else // not enough available products
                {
                    $pending_amount = $product['AMOUNT'] - $amount;
                    // reserve all amount
                    $product['AMOUNT'] = $amount;
                    $product['STATUS'] = 'RESERVED';
                    $products_with_statuses[] = $product;
                    // add pending amount for this product
                    $product['AMOUNT'] = $pending_amount;
                    $product['STATUS'] = 'PENDING';
                    $products_with_statuses[] = $product;
                    $overall_status = 'PENDING_PRODUCTS';
                }
            }
            else // no available products
            {
                $overall_status = 'PENDING_PRODUCTS';
                $product['STATUS'] = 'PENDING';
                $products_with_statuses[] = $product;
            }
        }
        return new CRes(SUCCESS,'Products split by availability', ['PRODUCTS'=>$products_with_statuses,
                                                                                'OVERALL_STATUS'=>$overall_status]);
    }
    
    public function get_order_status($order_id)
    {
        if(!isset($order_id))
            throw new CError("Empty func arg : order_id",
                             EMPTY_FUNCTION_ARG);
        $q = "SELECT * FROM {$this->t_orders()} WHERE `ID`={$order_id}
AND `PROJECT_ID`={$this->project_id} AND `WAREHOUSE_ID`={$this->warehouse_id}
ORDER BY LAST_MODIFIED DESC LIMIT 1;";
        $row = $this->db->CQuery($q)->Fetch();
        if(!$row)
            throw new CError("No order with order_id: {$order_id}",
                             NO_ORDER_ID);
        $status = $this->statuses->get_by_id($row['STATUS_ID']);
    
        return new CRes( SUCCESS, "Status order with order_id:{$order_id}", [   'ORDER_STATUS' => $status,
                                                                                           'FULL_INFO' => $row
                                                                                         ] );
        
    }
    
    /**
     * Get info about order status, and for each product in order
     * @param $order_id
     * @return CRes If no exceptions, return SUCCESS with data: ORDER_STATUS, ORDER_PRODUCTS
     * @throws CError
     */
    public function get_order_info($order_id)
    {
        if(!isset($order_id) || !$order_id)
            throw new CError("Empty func arg : order_id", EMPTY_FUNCTION_ARG);
        $order_status  =  $this->get_order_status($order_id)->data['ORDER_STATUS'];
        $q = "SELECT `ID`,`PRODUCT_ID`,`STATUS_ID` FROM {$this->t_order_products()} WHERE `ORDER_ID`={$order_id} AND
`STATUS_ID` <> {$this->statuses->get('ORDER_PRODUCTS','CANCELED')->id} AND
`PROJECT_ID`={$this->project_id} AND `WAREHOUSE_ID`={$this->warehouse_id};";
        $rows = $this->db->CQuery($q)->FetchByKey();
        if(!$rows)
            throw new CError("No products in order", NO_PRODUCTS_FOR_ORDER);
        $products = [];
        foreach($rows as &$row)
        {
            $row['STATUS'] = $this->statuses->get_by_id($row['STATUS_ID']);
            $products[] = $row;
        }
        return new CRes(  SUCCESS,"Products for order_id:{$order_id}", [
            'ORDER_STATUS'=>$order_status,
            'ORDER_PRODUCTS'=>$rows
        ] );
    }
    
    /**
     * Blind update status to CANCELED for each product for given order_id
     * @param $order_id
     * @return CRes Return SUCCESS anyway, if exception not caused
     * @throws CError
     */
    public function cancel_products($order_id)
    {
        if(!isset($order_id))
            throw new CError("Empty func arg : order_id",
                             EMPTY_FUNCTION_ARG);
        
//         $q = "UPDATE {$this->t_order_products()} SET `STATUS_ID`={$this->statuses->get('ORDER_PRODUCTS','CANCELED')->id}
        //WHERE `ORDER_ID`={$order_id} AND `PROJECT_ID`={$this->project_id} AND `WAREHOUSE_ID`={$this->warehouse_id}";
        //        $this->db->CQuery($q);
        //        return new CRes(SUCCESS, "Products for order_id: {$order_id} successfully canceled");
        $q = "DELETE FROM {$this->t_order_products()} WHERE `ORDER_ID`={$order_id} AND `PROJECT_ID`={$this->project_id} AND `WAREHOUSE_ID`={$this->warehouse_id}";
        $this->db->CQuery($q);
        return new CRes(SUCCESS, "Products for order_id: {$order_id} successfully canceled");
    }
    
    public function insert_products_for_order($products,$order_id)
    {
        if(!isset($products))
            throw new CError('Empty func arg : empty products',
                             EMPTY_FUNCTION_ARG);
        if(!isset($order_id))
            throw new CError("Empty func arg : order_id",
                             EMPTY_FUNCTION_ARG);
        if(!$products)
            throw new CError('Empty products',
                             EMPTY_PRODUCTS);
    
        $insert_data = [];
        foreach($products as $k=>$product)
        {
            $amount = intval($product['AMOUNT']);
            if(!$amount)
                throw new CError('Empty amount of product',
                                 EMPTY_PRODUCT_AMOUNT);
            while($amount--)
            {
                if(!is_numeric($product['PRODUCT_ID']))
                    throw new CError('product_id not numeric',
                                     PRODUCT_ID_NOT_NUMERIC);
                if(!isset($product['STATUS']))
                    throw new CError('No product status for create order',
                                     NO_PRODUCT_STATUS);
                $insert_data[] = [
                    'ORDER_ID'=>$order_id,
                    'PROJECT_ID'=>$this->project_id,
                    'WAREHOUSE_ID'=>$this->warehouse_id,
                    'STATUS_ID'=>$this->statuses->get('ORDER_PRODUCTS',$product['STATUS'])->id,
                    'PRODUCT_ID'=>$product['PRODUCT_ID'],
                ];
            }
        }
        $keys = ['ORDER_ID','PROJECT_ID','WAREHOUSE_ID','STATUS_ID','PRODUCT_ID'];
        $this->db->InsertMulti( $this->t_order_products(), $keys, $insert_data );
        $inserted_rows = $this->db->affected_rows();
        if(!$inserted_rows)
            throw new CError("Products for order:{$order_id} not inserted",
                             MYSQL_INSERT_ERROR);
        return new CRes( SUCCESS, "Successfully insert {$inserted_rows} rows for order_id:{$order_id}" );
    }
    
    /**
     * Blind update order status
     * @param $order_id
     * @param $order_status
     * @param $deadline
     */
    public function update_order_state($order_id,$order_status,$deadline=false)
    {
        $deadline_str = $deadline ? " , `DEADLINE`=FROM_UNIXTIME({$deadline}) " : '';
        $q = "UPDATE {$this->t_orders()} SET `STATUS_ID`={$this->statuses->get('ORDER',$order_status)->id} {$deadline_str}
WHERE `ID`={$order_id} AND `PROJECT_ID`={$this->project_id} AND `WAREHOUSE_ID`={$this->warehouse_id};";
        $this->db->CQuery($q);
    }
    
    /**
     * Check order products status, and if not canceled or pending, set order status to PENDING_COMPLECTATION
     * @param $order_id
     * @return CRes SUCCESS if order status updated, DEF_ERROR if order have pending products
     * @throws CError
     */
    public function refresh_order_complectation($order_id)
    {
        if(!isset($order_id))
            throw new CError("Empty func arg : order_id",
                             EMPTY_FUNCTION_ARG);
        $q = "SELECT `ID` as `ORDER_PRODUCTS_ID`,`PRODUCT_ID` FROM {$this->t_order_products()} WHERE `ORDER_ID`={$order_id} AND
`STATUS_ID`={$this->statuses->get('ORDER_PRODUCTS','PENDING')->id} AND
`STATUS_ID`<>{$this->statuses->get('ORDER_PRODUCTS','CANCELED')->id} AND
`PROJECT_ID`={$this->project_id} AND `WAREHOUSE_ID`={$this->warehouse_id};";
        $rows = $this->db->CQuery($q)->FetchByKey();
        if($rows)
            return new CRes(DEF_ERROR, "Order with ID:{$order_id} have pending products",$rows);
        $this->update_order_state($order_id,'PENDING_COMPLECTATION');
        return new CRes(SUCCESS, "Products for order_id:{$order_id} all reserved");
    }
    
    /**
     * Cancel order and linked products
     * @param $order_id
     */
    public function cancel_order($order_id)
    {
        $this->update_order_state($order_id,'CANCELED');
        $this->cancel_products($order_id);
    }
    
    /**
     * Get orders id and deadline for given statuses
     * @param $statuses
     * @return CRes Return DEF_ERROR if no orders, else SUCCESS with orders
     * @throws CError
     */
    public function get_orders_by_statuses($statuses)
    {
        if(!isset($statuses))
            throw new CError("Empty func arg : statuses",
                             EMPTY_FUNCTION_ARG);
        $statuses_ids = implode(',',$this->statuses->get_ids('ORDER',$statuses));
        $q = "SELECT `ID`,`DEADLINE` FROM {$this->t_orders()} WHERE
`STATUS_ID` in ({$statuses_ids}) AND `DEADLINE` IS NOT NULL AND
`PROJECT_ID`={$this->project_id} AND `WAREHOUSE_ID`={$this->warehouse_id} ORDER BY `DEADLINE` DESC;";
        $rows = $this->db->CQuery($q)->FetchByKey();
        if(!$rows)
            return new CRes(DEF_ERROR, " No orders with statuses: ".implode(',',$statuses));
        return new CRes(SUCCESS,"Orders with statuses: ".implode(',',$statuses), $rows);
    }
    
    /**
     * Reserve single product for given order_id, if product pending
     * @param $order_id
     * @param $product_id
     * @return CRes Return SUCCESS if product_id reserved for order and order refreshed, DEF_ERROR otherwise
     * @throws CError
     */
    public function reserve_product_for_order($order_id,$product_id)
    {
        if(!isset($order_id))
            throw new CError("Empty func arg : order_id",
                             EMPTY_FUNCTION_ARG);
        if(!isset($product_id))
            throw new CError("Empty func arg : product_id",
                             EMPTY_FUNCTION_ARG);
        $q = "SELECT `ID`,`PRODUCT_ID` FROM {$this->t_order_products()} WHERE `ORDER_ID`={$order_id} AND
`PRODUCT_ID`={$product_id} AND `STATUS_ID`={$this->statuses->get('ORDER_PRODUCTS','PENDING')->id} AND
`PROJECT_ID`={$this->project_id} AND `WAREHOUSE_ID`={$this->warehouse_id} LIMIT 1;";
        $row = $this->db->CQuery($q)->Fetch();
        if(!$row)
            return new CRes(DEF_ERROR, "Product_id: {$product_id} not reserved for order_id: {$order_id}");
        $this->db->Update($this->t_order_products(),
                          ['STATUS_ID' => $this->statuses->get('ORDER_PRODUCTS',
                                                               'RESERVED')->id
                          ],
                          "`ID`={$row['ID']}");
        //FIXME maybe it needs to do in inner scope
        $this->refresh_order_complectation($order_id);
        return new CRes(SUCCESS, "Product_id: {$product_id} reserved for order_id: {$order_id}");
    }
    
    /**
     * Create product, ignore if product exists
     * @param $id
     * @param $brand
     * @param $sku
     * @param $name
     * @param bool $barcode
     * @return CRes
     * @throws CError If product exists. Only if autoincrement set for product table in db
     */
    public function create_new_product($id, $brand, $sku, $name, $barcode = false)
    {
        if(!isset($sku))
            throw new CError("Empty func arg : sku",
                             EMPTY_FUNCTION_ARG);
        if(!isset($name))
            throw new CError("Empty func arg : name",
                             EMPTY_FUNCTION_ARG);
        if(!isset($brand))
            $brand = $this->s_default_brand();
        $insert_data = [
            'BRAND'=>$brand,
            'SKU'=>$sku,
            'NAME'=>$name,
            'PROJECT_ID'=>$this->project_id,
        ];
        if(isset($id)) //autoincrement not set in db
            $insert_data['ID'] = $id;
        if($barcode)
            $insert_data['BARCODE'] = $barcode;
        $insert_res = $this->db->InsertEx($this->t_products(),$insert_data);
        if(!$insert_res && !isset($id))
            throw new CError("Product not created brand:{$brand} sku:{$sku} name:{$name}",
                             MYSQL_INSERT_ERROR);
        return new CRes( SUCCESS, "New product created ID:{$insert_res}" );
    }
    
    /**
     * Create new item. Inner system need to pass received_task because items cant exists without
     * @param $received_task_id
     * @param $product_id
     * @param mixed $price
     * @param mixed $gtd
     * @param mixed $country_id
     * @param mixed $tn_id
     * @param mixed $address_id
     * @param mixed $nds
     * @param mixed $nds_sum
     * @return CRes Return always SUCCESS, if Exception not occurs
     * @throws CError
     */
    public function create_new_item($received_task_id, $product_id, $price=false, $nds = false, $nds_sum = false,
        $address_id = false, $gtd = false, $country_id = false, $tn_id = false)
    {
        if(!isset($received_task_id))
            throw new CError("Empty func arg : received_task_id",
                             EMPTY_FUNCTION_ARG);
        if(!isset($product_id))
            throw new CError("Empty func arg : product_id",
                             EMPTY_FUNCTION_ARG);
        $address_id = $address_id ?: $this->s_default_receive_address();
        $nds = $nds ?: $this->s_default_nds();
        
        $insert_data = [
            'WAREHOUSE_ID'=>$this->warehouse_id,
            'PRODUCT_ID'=>$product_id,
            'ADDRESS_ID'=>$address_id,
            'STATUS_ID'=>$this->statuses->get('ITEM','ON_RECEIVE_TASK')->id,
            'PRICE'=>$price,
            'NDS'=>$nds,
            'RECEIVE_TASK_ID'=>$received_task_id,
        ];
        $insert_data['NDS_SUM'] = $nds_sum ?: $price*$nds ;
        if($tn_id)
            $insert_data['TN_ID'] = $tn_id;
        if($gtd)
            $insert_data['GTD'] = $gtd;
        if($country_id)
            $insert_data['COUNTRY_ID'] = $country_id;
        
        $new_unique_item_id = $this->db->InsertEx($this->t_items(),$insert_data);
        if(!$new_unique_item_id)
            throw new CError("Item not created product_id: {$product_id}, received_task_id: {$received_task_id}",
                             MYSQL_INSERT_ERROR);
        return new CRes( SUCCESS, "New item created with unique ID:{$new_unique_item_id}", $new_unique_item_id );
    }
    
    public function api_get_products_by_sku($sku)
    {
        if(!isset($sku))
            throw new CError("Empty func arg : sku",
                             EMPTY_FUNCTION_ARG);
        
        $curlOpt = [
            CURLOPT_RETURNTRANSFER=>true,
            CURLOPT_HEADER=>false,
            CURLOPT_CONNECTTIMEOUT=>1,
        ];
    
        $request = "{$this->s_inner_api_point()}?ACTION=ajaxGetProductsBySku&SKU={$sku}&TOKEN={$this->s_inner_api_token()}";
        $ch = curl_init($request);
        foreach($curlOpt as $_curlConstName=>$_curlConstVal)
        {
            curl_setopt($ch, $_curlConstName, $_curlConstVal);
        }
        
        $t = microtime(true);
        $rawResponse = curl_exec($ch);
        curl_close($ch);
        $timeDelta = microtime(true) - $t;
        $responseJSON = json_decode($rawResponse,true);
        $res_code = DEF_ERROR;
        $msg = "Error response for sku: {$sku}";
        $res_data = false;
        if($responseJSON)
        {
            $code = $responseJSON['code'];
            if($code == 200)
            {
                $res_code = SUCCESS;
                $msg = "Response for sku: {$sku}";
                $res_data = $responseJSON['response'];
            }
            else
                $msg = $responseJSON['response'];
            
        }
        return new CRes($res_code,$msg,['RESPONSE'=>$res_data, 'TIME'=>$timeDelta]);
    }
    
    public function get_products_by_sku($sku)
    {
        if(!isset($sku))
            throw new CError("Empty func arg : sku",
                             EMPTY_FUNCTION_ARG);
    
        $q="SELECT `ID` as `PRODUCT_ID`,`SKU`,`BRAND`,`NAME`,`BARCODE` FROM {$this->t_products()} WHERE `SKU`='{$sku}' AND
`PROJECT_ID`={$this->project_id} ORDER BY `BRAND`;";
        $rows = $this->db->CQuery($q)->FetchByKey();
        if(!$rows)
            return new CRes(DEF_ERROR, "No products for sku: {$sku}");
        $products = [];
        foreach($rows as $row)
        {
            if(!isset($products[$row['BRAND']]))
                $products[$row['BRAND']] = [];
            $products[$row['BRAND']][] = $row;
        }
        return new CRes( SUCCESS, "Products for sku: {$sku}", $products );
    }
    
    /**
     * Get product by id
     * @param $product_id
     * @return CRes Return SUCCESS if product with id exists, DEF_ERROR otherwise
     * @throws CError
     */
    public function get_product_by_id($product_id)
    {
        if(!isset($product_id))
            throw new CError("Empty func arg : product_id",
                             EMPTY_FUNCTION_ARG);
    
        $q="SELECT `ID` as `PRODUCT_ID`,`SKU`,`BRAND`,`NAME`,`BARCODE` FROM {$this->t_products()} WHERE `ID`='{$product_id}' AND
`PROJECT_ID`={$this->project_id};";
        $row = $this->db->CQuery($q)->Fetch();
        if(!$row)
            return new CRes( DEF_ERROR, "No product with ID: {$product_id}");
        return new CRes( SUCCESS, "Product with ID: {$product_id}", $row);
    }
    
    /**
     * Update product fields
     * @param $product_id
     * @param $field_values
     * @return CRes Return SUCCESS if exception not occurs
     * @throws CError
     */
    public function update_product($product_id,$field_values)
    {
        if(!isset($product_id))
            throw new CError("Empty func arg : product_id",
                             EMPTY_FUNCTION_ARG);
        if(!isset($field_values))
            throw new CError("Empty func arg : field_values",
                             EMPTY_FUNCTION_ARG);
        $this->db->Update($this->t_products(),$field_values, "`ID`={$product_id}");
        return new CRes( SUCCESS, "Product with ID: {$product_id} updated");
    }
    
    /**
     * Update item by id, if any value given
     * @param $item_id
     * @param bool $address_id
     * @param bool $status
     * @param bool $tn_id
     * @param bool $order_id
     * @param bool $price
     * @param bool $nds
     * @param bool $nds_sum
     * @param bool $gtd
     * @param bool $country_id
     * @param bool $supplier_id
     * @param bool $receive_task_id
     * @return CRes Return SUCCESS if updated, DEF_ERROR if no values given
     * @throws CError
     */
    public function update_item($item_id,$address_id=false,$status=false,$tn_id=false,$order_id=false,
        $price=false,$nds=false,$nds_sum=false,$gtd=false,$country_id=false,$supplier_id=false,$receive_task_id=false)
    {
        if(!isset($item_id) || !$item_id)
            throw new CError("Empty func arg : item_id",
                             EMPTY_FUNCTION_ARG);
        $update_data = [];
        if($address_id)
            $update_data['ADDRESS_ID'] = $address_id;
        if($status)
            $update_data['STATUS_ID'] = $this->statuses->get('ITEM',$status)->id;
        if($tn_id)
            $update_data['TN_ID'] = $tn_id;
        if($order_id)
            $update_data['ORDER_ID'] = $order_id;
        if($price)
            $update_data['PRICE'] = $price;
        if($nds)
            $update_data['NDS'] = $nds;
        if($nds_sum)
            $update_data['NDS_SUM'] = $nds;
        if($gtd)
            $update_data['GTD'] = $gtd;
        if($country_id)
            $update_data['COUNTRY_ID'] = $country_id;
        if($supplier_id)
            $update_data['SUPPLIER_ID'] = $supplier_id;
        if($receive_task_id)
            $update_data['RECEIVE_TASK_ID'] = $receive_task_id;
        
        if(!$update_data)
            return new CRes(DEF_ERROR,"Nothing to update");
        
        $this->db->Update($this->t_items(),$update_data,"`ID`={$item_id}");
        return new CRes(SUCCESS, "Item with ID={$item_id}");
    }
    
    /**Tasks**/
    /**
     * Create new receive task for user_id
     * @param $user_id
     * @param $date_receive
     * @return CRes Return new task_id
     * @throws CError
     */
    public function create_new_receive_task($user_id, $date_receive=false)
    {
        if(!isset($user_id))
            throw new CError("Empty func arg : user_id",
                             EMPTY_FUNCTION_ARG);
        $insert_data = [
            'USER_ID'=>$user_id,
            'PROJECT_ID'=>$this->project_id,
            'WAREHOUSE_ID'=>$this->warehouse_id,
            'DATE_RECEIVE'=>'FROM_UNIXTIME('.($date_receive ? $date_receive : time()).')',
            'STATUS_ID'=>$this->statuses->get('RECEIVE_TASK','IN_PROGRESS')->id
        ];
        $new_task_id = $this->db->InsertEx($this->t_receive_tasks(),$insert_data);
        if(!$new_task_id)
            throw new CError("Item not created receive task for user_id: {$user_id}",
                             MYSQL_INSERT_ERROR);
        return new CRes(SUCCESS, "New receive task created with ID: {$new_task_id}", $new_task_id);
    }
    
    /**
     * Cancel receive_task
     * @param $task_id
     * @return CRes
     * @throws CError
     */
    public function cancel_receive_task($task_id)
    {
        if(!isset($task_id))
            throw new CError("Empty func arg : task_id",
                             EMPTY_FUNCTION_ARG);
        $update = [
            'STATUS_ID'=>$this->statuses->get('RECEIVE_TASK','CANCELED')->id
        ];
        $this->db->Update($this->t_receive_tasks(),$update,"`ID`={$task_id}");
        $delete = "DELETE FROM {$this->t_items()} WHERE `RECEIVE_TASK_ID`={$task_id};";
        $this->db->CQuery($delete);
       return new CRes(SUCCESS, "Cancel receive task with ID: {$task_id}");
    }
    
    /**
     * Set task status to SUCCESS and set COMPLETE_AT time
     * @param $task_id
     * @param $status
     * @return CRes If not exceptions, always return SUCCESS
     * @throws CError
     */
    public function complete_receive_task($task_id, $status)
    {
        if(!isset($task_id))
            throw new CError("Empty func arg : task_id",
                             EMPTY_FUNCTION_ARG);
        if(!isset($status))
            throw new CError("Empty func arg : status",
                             EMPTY_FUNCTION_ARG);
        
        $this->db->Update($this->t_receive_tasks(),[
            'STATUS_ID'=>$this->statuses->get('RECEIVE_TASK', 'SUCCESS')->id,
            'COMPLETE_AT'=>'FROM_UNIXTIME('.time().')'
        ], "`ID`={$task_id}");
        return new CRes(SUCCESS, "Receive task with ID: {$task_id} successful complete");
    }
    
    /**
     * Create or update tn for receive_task_id. If create, update TN_ID for receive task.
     * @param $receive_task_id
     * @param $tn_number
     * @param $invoice_number
     * @param $tn_sum
     * @param $tn_date
     * @param $supplier_id
     * @param bool $nds
     * @param bool $nds_sum
     * @param bool $img_id
     * @return CRes [STATUS=>create|update,TN_ID=>created or updated tn_id]
     * @throws CError
     */
    public function create_tn($receive_task_id,$tn_number,$invoice_number,$tn_sum,$tn_date,$supplier_id,$nds=false,
        $nds_sum=false, $img_id=false)
    {
        if(!isset($receive_task_id))
            throw new CError("Empty func arg : receive_task_id",
                             EMPTY_FUNCTION_ARG);
        if(!isset($tn_number))
            throw new CError("Empty func arg : tn_number",
                             EMPTY_FUNCTION_ARG);
        if(!isset($invoice_number))
            throw new CError("Empty func arg : invoice_number",
                             EMPTY_FUNCTION_ARG);
        if(!isset($tn_sum))
            throw new CError("Empty func arg : tn_sum",
                             EMPTY_FUNCTION_ARG);
        if(!isset($tn_date))
            throw new CError("Empty func arg : tn_date",
                             EMPTY_FUNCTION_ARG);
        if(!isset($supplier_id))
            throw new CError("Empty func arg : supplier_id",
                             EMPTY_FUNCTION_ARG);
        
        $q = "SELECT `ID` FROM {$this->t_tns()} WHERE `RECEIVE_TASK_ID`={$receive_task_id};";
        $row = $this->db->CQuery($q)->Fetch();
        $tn_id = false;
        if($row)
            $tn_id = intval($row['ID']);
        
        $upsert_data = [
            'RECEIVE_TASK_ID'=>$receive_task_id,
            'TN_NUMBER'=>$tn_number,
            'INVOICE_NUMBER'=>$invoice_number,
            'SUM'=>$tn_sum,
            'TN_DATE'=>"FROM_UNIXTIME({$tn_date})",
            'SUPPLIER_ID'=>$supplier_id,
            'PROJECT_ID'=>$this->project_id,
            'WAREHOUSE_ID'=>$this->warehouse_id,
        ];
        $nds = $nds ?: $this->s_default_nds();
        $upsert_data['NDS'] = $nds ;
        $upsert_data['NDS_SUM'] = $nds_sum ?: $tn_sum*$nds ;
        $upsert_data['SUM_WITH_NDS'] = $tn_sum + $upsert_data['NDS_SUM'];
        if($img_id)
            $upsert_data['IMG_ID'] = $img_id;
        
        $result_status = 'updated';
        if($tn_id)
            $this->db->Update($this->t_tns(),$upsert_data,"`ID`={$tn_id}");
        else
        {
            $result_status = 'created';
            $tn_id = $this->db->InsertEx($this->t_tns(),$upsert_data,false);
            if(!$tn_id)
                throw new CError("New TN not created for receive_task: {$receive_task_id}",
                                 MYSQL_INSERT_ERROR);
            $this->db->Update($this->t_receive_tasks(),['TN_ID'=>$tn_id,'SUPPLIER_ID'=>$supplier_id],"`ID`={$receive_task_id}");
        }
        return new CRes(SUCCESS, "TN {$result_status} for receive_task: {$receive_task_id}",
                        ['STATUS'=>$result_status,'TN_ID'=>$tn_id]);
    }
    
    /**
     * Get items for receive_task_id in status ON_RECEIVE_TASK
     * @param $receive_task_id
     * @return CRes Return items if exists, DEF_ERROR otherwise
     * @throws CError
     */
    public function get_items_by_receive_task($receive_task_id)
    {
        if(!isset($receive_task_id))
            throw new CError("Empty func arg : receive_task_id",
                             EMPTY_FUNCTION_ARG);
        $q = "SELECT * FROM {$this->t_items()} WHERE `RECEIVE_TASK_ID`={$receive_task_id} AND
`STATUS_ID`={$this->statuses->get('ITEM','ON_RECEIVE_TASK')->id} AND `WAREHOUSE_ID`={$this->warehouse_id};";
        $rows = $this->db->CQuery($q)->FetchByKey();
        if(!$rows)
            return new CRes(DEF_ERROR, "No items for {$receive_task_id}");
        return new CRes(SUCCESS, "Items for {$receive_task_id}",$rows);
    }
    
}
