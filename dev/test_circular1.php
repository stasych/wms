<?
require_once $_SERVER['DOCUMENT_ROOT'].'/dev/test_circular2.php';

interface ITest1
{
    public function set_test2(ITest2 &$test2);
}