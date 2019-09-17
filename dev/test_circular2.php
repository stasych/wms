<?
require_once $_SERVER[ 'DOCUMENT_ROOT' ] . '/dev/test_circular1.php';

interface ITest2
{
    public function set_test1(ITest1 &$test1);
}