<?
require_once $_SERVER['DOCUMENT_ROOT'].'/src/classes/CDB/CDB.php';
//$db = new CDB();
//$res = $db->CQuery("SELECT * FROM z_test;")->FetchByKey();
//dumpEx($res);

// datetime tests
$ts = DateTime::createFromFormat('Y-m-d H:i:s','2017-07-02 14:00:53')->getTimestamp();
dumpEx($ts);

$dt = date('Y-m-d H:i:s', strtotime(' +1 day'));
$ts = strtotime($dt);
dumpEx($ts,"Timestamp from date: {$dt}");

//-----------------
$a = ['a'=>[]];
$res = 'default';
if(!isset($a['a']))
{
    $res='a not isset';
}
elseif(!$a['a'])
{
    $res='a is empty';
}
dumpEx($res);

require_once $_SERVER['DOCUMENT_ROOT'].'/src/classes/CRes/CRes.php';
$data = [
    'OBJECT_ARRAY'=>[
                        new CRes(SUCCESS,'Object1'),
                        new CRes(DEF_ERROR,'Object2')
                    ],
];

$res = new CRes(SUCCESS,'Object3', $data);
dumpEx($res,'Test dump object');