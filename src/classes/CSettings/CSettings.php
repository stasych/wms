<?
require_once $_SERVER['DOCUMENT_ROOT' ] . "/src/classes/CCore/globals.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/src/classes/CError/CError.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/src/classes/CRes/CRes.php";

class CSettings
{
    public $settings = [];
    public static $table = 'project_settings';
    public function table(){ return self::$table; }
    
    public function __construct($project_id,$warehouse_id=false)
    {
        if(!is_numeric($project_id))
            throw new CError('project_id not numeric',
                             PROJECT_ID_NOT_NUMERIC);
        if($warehouse_id && !is_numeric($project_id))
            throw new CError('warehouse not numeric',
                             PROJECT_ID_NOT_NUMERIC);
        global $DB;
        $q = "SELECT * FROM {$this->table()} WHERE `PROJECT_ID`={$project_id} AND `WAREHOUSE_ID` is NULL;";
        $row = $DB->CQuery($q)->Fetch();
        if($row && $row['DATA'])
            $this->settings = json_decode($row['DATA'],true);
        if($warehouse_id)
        {
            $q = "SELECT * FROM {$this->table()} WHERE `PROJECT_ID`={$project_id} AND `WAREHOUSE_ID`={$warehouse_id};";
            $row = $DB->CQuery($q)->Fetch();
            if($row && $row['DATA'])
            {
                $warehouse_settings = json_decode($row['DATA'],true);
                $this->settings = array_merge($this->settings,$warehouse_settings);
            }
        }
    }
    
    public function is($key){ return array_key_exists($key,$this->settings);}
    public function get($key){ return $this->is($key) ? $this->settings[$key] : false;}
}