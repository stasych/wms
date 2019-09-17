<?
require_once $_SERVER['DOCUMENT_ROOT'].'/src/classes/queue/IQueue.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/src/classes/CCore/globals.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/src/classes/CError/CError.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/src/classes/CRes/CRes.php';

class PrintEventData implements IEventData
{
    private $event_id = false;
    private $event_type = false;
    private $project_id = false;
    private $warehouse_id = false;
    private $created_at = false;
    private $data = false;
    private $result_data = false;
    private $version = 'current';
    
    public function __construct($raw_data, $decode_data=true)
    {
        if(isset($raw_data['ID']))
            $this->event_id = $raw_data['ID'];
        
        if(!isset($raw_data['EVENT_TYPE']))
            throw new CError('Empty EVENT_TYPE in event data',
                             EMPTY_EVENT_TYPE);
        $this->event_type = $raw_data['EVENT_TYPE'];
        
        if(!isset($raw_data['PROJECT_ID']))
            throw new CError('Empty PROJECT_ID in event data',
                             EMPTY_PROJECT_ID);
        $this->project_id = $raw_data['PROJECT_ID'];
        
        if(!isset($raw_data['WAREHOUSE_ID']))
            throw new CError('Empty WAREHOUSE_ID in event data',
                             EMPTY_WAREHOUSE_ID);
        $this->warehouse_id = $raw_data['WAREHOUSE_ID'];
        
        if(!isset($raw_data['DATA']))
            throw new CError('Empty DATA in event data',
                             EMPTY_EVENT_DATA);
    
        if(isset($raw_data['RESULT_DATA']))
            $this->result_data = $decode_data ? json_decode($raw_data['RESULT_DATA'],true) : $raw_data['RESULT_DATA'];
        
        if(isset($raw_data['CREATED_AT']))
            $this->created_at = $raw_data['CREATED_AT'];
    
        if(isset($raw_data['VERSION']))
            $this->version = $raw_data['VERSION'];
        
        
        $this->data = $decode_data ? json_decode($raw_data['DATA'],true) : $raw_data['DATA'];
    }
    
    public function get_event_id(){ return $this->event_id; }
    
    public function get_event_class(){ return $this->event_type; }
    
    public function get_event_project(){ return $this->project_id; }
    
    public function get_event_warehouse(){ return $this->warehouse_id; }
    
    public function get_event_data(){ return $this->data; }
    
    public function get_event_result_data(){ return $this->result_data; }
    
    public function get_event_version(){ return $this->version; }
    
}
class MySQLPrintQueue implements IQueue
{
    private $factory = null;
    private $table = 'print_queue';
    private function t_table(){return $this->table;}
    public $db = null;
    private $max_exec_time = 30;
    
    public function __construct(IFactory $factory = null)
    {
        global $DB;
        $this->db = &$DB;
        if(!is_null($factory))
            $this->set_factory($factory);
    }
    
    public function set_factory(IFactory $factory)
    {
        $this->factory = $factory;
        $this->factory->set_queue($this);
    }
    
    /**
     * Push event to queue
     * @param $data
     * @return CRes Return new event id
     * @throws CError
     */
    public function push($data)
    {
        $event_data = new PrintEventData($data,false);
        $escaped_data = $this->db->Escape(json_encode($event_data->get_event_data(), JSON_UNESCAPED_UNICODE |
                                                                           JSON_FORCE_OBJECT));
        $insert_data = [
            'EVENT_TYPE'=>$event_data->get_event_class(),
            'PROJECT_ID'=>$event_data->get_event_project(),
            'WAREHOUSE_ID'=>$event_data->get_event_warehouse(),
            'DATA'=>$escaped_data,
            'RESULT_DATA'=>[],
        ];
        $insert_res = $this->db->InsertEx($this->t_table(),$insert_data);
        if(!$insert_res)
            throw new CError('Push event_data error',
                             PUSH_EVENT_ERROR);
        return new CRes(SUCCESS,"New event_id: {$insert_res}", $insert_res);
    }
    
    public function pop() : IEventData
    {
        //get last
        $q = "SELECT `ID`,`DATA`,`EVENT_TYPE`,`PROJECT_ID`,`WAREHOUSE_ID`,FROM_UNIXTIME(`CREATED_AT`) as `CREATED_AT` FROM {$this->t_table()}
WHERE `PROCESSED`=0
ORDER BY `ID` LIMIT 1;";
        $row = $this->db->CQuery($q)->Fetch();
        if(!$row)
            throw new CError('Empty queue',
                             EMPTY_QUEUE);
        //lock event from further processing
        $upd_res = $this->db->Update($this->t_table(),['PROCESSED'=>1,'PROCESSED_AT'=>'FROM_UNIXTIME('.time().')'],"`ID`={$row['ID']}");
        if(!$upd_res)
            throw new CError('Event not processed',
                             EVENT_NOT_PROCESSED);
        return new PrintEventData($row);
    }
    
    public function pop_event() : IEvent
    {
        if(is_null($this->factory))
            throw new CError('No event factory in queue object',
                             EVENT_FACTORY_NOT_SET);
        
        return $this->factory->produce($this->pop());
    }
    
    public function set_event_result($event_id,$data)
    {
        if(!isset($event_id))
            throw new CError('Empty func arg : event_id',
                             EMPTY_FUNCTION_ARG);
        if(!isset($data))
            throw new CError('Empty func arg : data',
                             EMPTY_FUNCTION_ARG);
        $this->db->UpsertJSON($this->t_table(),$data,"`ID`={$event_id}",'RESULT_DATA');
        $this->db->Update($this->t_table(),['HAS_RESULT'=>1],"`ID`={$event_id}");
    }
    
    public function get_event_result($event_id, $max_exec_time=false)
    {
        set_time_limit($max_exec_time?:$this->max_exec_time);
        $q = "SELECT `HAS_RESULT`,`ID`,`PROCESSED`,`DATA`,`RESULT_DATA`,`EVENT_TYPE`,`PROJECT_ID`,`WAREHOUSE_ID`,FROM_UNIXTIME
(`CREATED_AT`) as `CREATED_AT` FROM {$this->t_table()}
WHERE `ID`={$event_id};";
        while(true)
        {
            $row = $this->db->CQuery($q)->Fetch();
            if(!$row)
                throw new CError("Not exists event id: {$event_id}",
                                 WRONG_EVENT_ID);
            elseif($row['HAS_RESULT'] == 1)
                break;
        }
       return new PrintEventData($row);
    }
}