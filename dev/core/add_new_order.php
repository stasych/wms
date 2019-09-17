<?
require_once $_SERVER['DOCUMENT_ROOT'].'/src/classes/CCore/CCore.php';
require_once $_SERVER[ 'DOCUMENT_ROOT' ] . '/src/utils/custom_utils.php';

$project_id = 1;
$warehouse_id = 1;
$core = new CCore($project_id,$warehouse_id);

$deadline = strtotime(date('Y-m-d H:i:s', strtotime(' +1 day')));
# Test case 1
$purchases =[];
$purchases[] = ['PRODUCT_ID'=>1,'AMOUNT'=>3,'STATUS'=>'PENDING'];
$purchases[] = ['PRODUCT_ID'=>2,'AMOUNT'=>1,'STATUS'=>'RESERVED'];
try
{
    $order_id = 1;
    $res = $core->add_new_order($purchases,$order_id,'PENDING_PRODUCTS', $deadline);
    dumpEx($res, 'Test case 1');
}
catch(Exception $e)
{
    dumpEx($e, 'Exception : Test case 1');
}

# Test case 2
$purchases =[];
$purchases[] = ['PRODUCT_ID'=>1,'AMOUNT'=>'asdf','STATUS'=>'PENDING'];
//$purchases[] = ['PRODUCT_ID'=>2,'AMOUNT'=>0];
try
{
    $order_id = -2;
    $res = $core->add_new_order( $purchases, $order_id, 'PENDING_PRODUCTS',$deadline);
    dumpEx($res, 'Test case 2');
}
catch(CError $e)
{
    dumpEx($e, 'CError : Test case 2');
}


# Test case 3
$purchases =[];
try
{
    $order_id = -3;
    $res = $core->add_new_order($purchases,$order_id,'PENDING_PRODUCTS',$deadline);
    dumpEx($res, 'Test case 3');
}
catch(Exception $e)
{
    dumpEx($e, 'Exception : Test case 3');
}

# Test case 4
$purchases =[];
$purchases[] = ['PRODUCT_ID'=>1,'AMOUNT'=>3,'STATUS'=>'PENDING'];
$purchases[] = ['PRODUCT_ID'=>2,'AMOUNT'=>1,'STATUS'=>'PENDING'];
try
{
    $order_id = 'WRONG ID';
    $res = $core->add_new_order($purchases,$order_id,'PENDING_PRODUCTS',$deadline);
    dumpEx($res, 'Test case 4');
}
catch(CError $e)
{
    dumpEx($e, 'CError : Test case 4');
}
catch(Exception $e)
{
    dumpEx($e, 'Exception : Test case 4');
}

# Test case 5
$purchases =[];
$purchases[] = ['PRODUCT_ID'=>1,'AMOUNT'=>3,'STATUS'=>'PENDING'];
$purchases[] = ['PRODUCT_ID'=>'asdf','AMOUNT'=>1,'STATUS'=>'PENDING'];
try
{
    $order_id = -5;
    $res = $core->add_new_order($purchases,$order_id,'PENDING_PRODUCTS',$deadline);
    dumpEx($res, 'Test case 5');
}
catch(Exception $e)
{
    dumpEx($e, 'CError : Test case 5');
}

# Test case 6
$purchases =[];
$purchases[] = ['PRODUCT_ID'=>3,'AMOUNT'=>1,'STATUS'=>'RESERVED'];
try
{
    $order_id = 2000;
    $res = $core->add_new_order($purchases,$order_id,'PENDING_COMPLECTATION',$deadline);
    dumpEx($res, 'Test case 6');
}
catch(Exception $e)
{
    dumpEx($e, 'Exception : Test case 6');
}

