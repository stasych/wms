<?
require_once $_SERVER['DOCUMENT_ROOT' ] . "/src/classes/CCore/globals.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/src/classes/CError/CError.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/src/classes/CRes/CRes.php";

class CStatus
{
    public $id = false;
    public $entity = false;
    public $type = false;
    public $title = false;
    public $description = false;
    
    public function __construct($id, $entity, $type, $title,  $description)
    {
        $this->id = $id;
        $this->entity = $entity;
        $this->type = $type;
        $this->title = $title;
        $this->description = $description;
    }
}

class CStatuses
{
    public $project_id = false;
    public $statuses = false;
    
    public static $table = 'statuses';
    public function table(){ return self::$table; }
    
    public function __construct($project_id)
    {
        if(!is_numeric($project_id))
            throw new CError('project_id not numeric',
                             PROJECT_ID_NOT_NUMERIC);
        global $DB;
        $q = "SELECT * FROM {$this->table()} WHERE `PROJECT_ID`={$project_id};";
        $rows = $DB->CQuery($q)->FetchByKey();
        if(!$rows)
        {
            throw new CError("No statuses for project_id : {$project_id}",
                             MYSQL_EMPTY_RESULT);
        }
        $this->statuses = [];
        foreach($rows as $row)
        {
            $id = $row['ID'];
            $title = $row['TITLE'];
            $entity = $row['ENTITY'];
            $type = $row['TYPE'];
            $description = $row['DESCRIPTION'];
            
            if(!isset($this->statuses[$entity]))
                $this->statuses[$entity] = [];
            
            $this->statuses[$entity][$type] = new CStatus($id, $entity, $type, $title, $description );
        }
    }
    
    public function  get($entity, $type) : CStatus
    {
        if(!isset($this->statuses[$entity]))
            throw new CError("No entity: [{$entity}]",
                             NO_ENTITY_IN_STATUSES);
        if(!isset($this->statuses[$entity][$type]))
            throw new CError("No type: [{$type}] for entity : {$entity}",
                             NO_TYPE_IN_STATUSES);
        return $this->statuses[$entity][$type];
    }
    
    public function get_by_id($status_id) : CStatus
    {
        $res = false;
        foreach($this->statuses as $entity=>$types)
            foreach($types as $status)
                if($status->id == $status_id)
                {
                    $res = $status;
                    break;
                }
        if(!$res)
            throw new CError("No status with status_id: {$status_id}",
                             NO_STATUS_WITH_ID);
        return $res;
    }
    
    public function get_ids($entity,$statuses)
    {
        $ids = [];
        foreach($statuses as $status)
            $ids[] = $this->get($entity,$status)->id;
        return $ids;
    }
    
}