<?
require_once '../src/utils/init_cli.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/dev/test_circular1.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/dev/test_circular2.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/src/utils/custom_utils.php';
//edebug(true);

class Test2 implements ITest2
{
    public $test1 = null;
    public function id(){ return __CLASS__.' '.spl_object_hash($this);}
    public function test(){ echo $this->id().PHP_EOL;}
    public function set_test1(ITest1 &$test1)
    {
        $this->test1 = $test1;
    }
}

class Test1 implements ITest1
{
    public $test2 = null;
    public function id(){ return __CLASS__.' '.spl_object_hash($this);}
    public function test(){ echo $this->id().PHP_EOL;}
    public function set_test2(ITest2 &$test2)
    {
        $this->test2 = $test2;
    }
}
$test1 = new Test1;
$test2 = new Test2;

$test1->set_test2($test2);
$test2->set_test1($test1);

$test1->test();
$test1->test2->test();
$test2->test();
$test2->test1->test();