<?
require_once $_SERVER['DOCUMENT_ROOT'].'/src/classes/events_factory/IFactory.php';

interface IQueue
{
    public function set_factory(IFactory $factory);
    
    public function pop();
    
    public function push($data);
    
    public function pop_event();
    
    public function set_event_result($event_id,$data);
    
}