<?
require_once $_SERVER[ 'DOCUMENT_ROOT' ] . '/src/utils/base_utils.php';
require_once $_SERVER[ 'DOCUMENT_ROOT' ] . '/src/utils/dumpEx.php';
class CDB
{
	static $log = [];
	private $lastresult = false;
	private $db = null;
	static $defdb = 'wms';
	static $defuser = 'wms_admin';
	static $defpass = 'pcw';
	static $defip = 'localhost';
	private $customdb = false;
	private $customuser = false;
	private $custompass = false;
	private $customip = false;
	private $debug = false;

	function __construct($db = false, $user = false, $pass = false, $ip = false)
	{
		$this->customdb = $db ?: self::$defdb;
		$this->customuser = $user ?: self::$defuser;
		$this->custompass = $pass ?: self::$defpass;
		$this->customip = $ip ?: self::$defip;
		$this->setVariables();
		$this->doDB();
	}

	function __destruct()
	{
        if($this->db !== false && $this->db->ping() )
		    $this->Close();
	}

	public function doDB()
	{
		if(cval($this->db) !== false)
		{
			if(!$this->db->ping())
			{
				$this->db = false;
				$this->doDB();
			}
			else
				return;
		}
		else
		{
			$this->db = new mysqli($this->customip, $this->customuser, $this->custompass, $this->customdb);
			if($this->db->connect_error)
			{
				$this->log('MySQL FAILED - ' . $this->db->connect_errno . ':' . $this->db->connect_error, __FUNCTION__);
				throw new Exception('MySQL FAILED - ' . $this->db->connect_errno . ':' . $this->db->connect_error);
			}
			$this->db->set_charset('utf8');
		}
	}
    
    public function query_error()
    {
        return $this->db->error;
    }

	public function error()
	{
		return $this->db->connect_error;
	}

	public function errno()
	{
		return $this->db->connect_errno;
	}

	public function ping()
	{
		$res = false;
		try
		{
			$res = $this->db->ping();
		}
		catch(Exception $E)
		{
			return false;
		}
		finally
		{
			return $res;
		}
	}

	public function EscapeString($data)
	{
		return $this->db->real_escape_string($data);
	}

	public function EscapeArray($data)
	{
		$ret_arr = [];
		foreach($data as $key => $value)
		{
			$ret_arr[$this->EscapeString($key)] = $this->Escape($value);
			unset($data[$key]);
		}
		return $ret_arr;
	}

	public function Escape($data)
	{
		if(is_array($data))
		{
			return $this->EscapeArray($data);
		}
		elseif(is_numeric($data) || is_bool($data) || is_null($data))
		{
			return $data;
		}
		else
		{
			return $this->EscapeString($data);
		}
	}

	public function GetLastResult()
	{
		return $this->lastresult;
	}

	public function Query($q)
	{
		$this->doDB();
		$this->lastresult = $this->coreQuery($q);
		$this->checkError($q);
		return $this->lastresult;
	}

	public function CQuery($q)
	{
		$this->doDB();
		$this->lastresult = $this->coreQuery($q);
		$this->checkError($q);
		return $this;
	}

	public $error = 0;
	public $errno = 0;
	private function checkError($q)
	{
		if($this->db->error && $this->debug)
		{
			$debugTrac = debug_backtrace();
			$calltext = [];
			foreach($debugTrac as $tracID => $tracData)
			{
				if($tracID == 0)
					continue;
				if(in_array(mb_strtolower($debugTrac[$tracID+1]['function']), ['includecomponent','includetemplate','showcomponenttemplate','includecomponenttemplate','executecomponent','__includephptemplate']))
					continue;
				$calltext[] = "Source: line {$debugTrac[$tracID]['line']} of file {$debugTrac[$tracID]['file']}, fnc: {$debugTrac[$tracID+1]['function']}\r\n";
			}
			$calltext = implode("\n", $calltext);
			$this->errno = $this->db->errno;
			$this->error = $this->db->error;
//			dumpEx($this->db->error . "\n" . $calltext, 'MYSQL ERROR ' . $this->db->errno, false, true);
//			dumpEx($q, 'ERROR FULL QUERY TEXT');
			$this->log('MySQL error ' . $this->db->errno . ' : ' . $this->db->error);
			$this->log('MySQL error full query text: ' . $q);
		}
	}

    public function prepareInsertValues($data)
    {
        $VALUES = [];
        foreach($data as $key => $value)
        {
            if(is_array($value))
			{
				$value = recursive_array_replace(["'",'"',"\\","/"], "", $value);
				$VALUES[] = wrapsql(json_encode($value, JSON_UNESCAPED_UNICODE | JSON_FORCE_OBJECT));
			}
            elseif(is_null($value))
                $VALUES[] = "NULL";
            elseif(strpos($value, 'FROM_UNIXTIME') !== false)
                $VALUES[] = $value;
            else
                $VALUES[] = wrapsql($value);
        }
        return implode(', ', $VALUES);
    }

    /**
     * Prepare multiupsert query
     * @param $table
     * @param $keys columns names
     * @param $values_pairs array of k-v pairs arrays
     * @param string $id field to exclude from update part of statement
     * @return bool|string
     */
    public function prepare_multi_upsert($table,$keys,$values_pairs,$id='ID')
    {
        if(!$values_pairs)
            return false;

        $columns = [];
        $upd = [];
        foreach($keys as $key)
        {
            $columns[] = '`'.$key.'`';
            if($key === $id)
                continue;
            $upd[] = "`{$key}`=VALUES(`$key`)";
        }

        $values = [];
        foreach($values_pairs as $pair)
        {
            $v=[];
            foreach($keys as $k)
            {
                $val = $pair[$k];
                if(is_null($val))
                {
                    $val = 'NULL';
                }
                elseif(strpos($val,'FROM_UNIXTIME') !== false)
                {
                    $val = $val;
                }
                else
                {
                    $val = "'{$val}'";
                }
                $v[] = $val;
            }
            $values[] = '(' . implode(',',$v) . ')';
        }

        $q = "INSERT INTO `{$table}` (". implode(',',$columns) .") VALUES " . implode(',',$values) . " ON DUPLICATE KEY UPDATE ". implode(',',$upd) . ";";

        return $q;
    }

	public function Insert($q)
	{
		$this->doDB();
		$this->lastresult = $this->coreQuery($q);
		$this->checkError($q);
		return $this->InsertID();
	}

	public function coreQuery($q)
	{
		if(!$this->db || !$this->ping())
		{
			$this->db = false;
			$this->doDB();
		}
		$this->error = 0;
		$this->errno = 0;
		return $this->db->query($q);
	}


	public function InsertEx($table, $data, $ignore = true)
	{
		if(!is_array($data) || scount($data) < 1 || !cval($table))
			return false;
		$this->doDB();

        $KEYS = implode(', ', array_map('wrapsqlfield', array_keys($data)));
		$VALUES = $this->prepareInsertValues($data);
        $duplicate = '';
        if(!$ignore)
		{
			$duplicateValues = [];
			foreach($data as $key => $value)
			{
				$duplicateValues[] = wrapsqlfield($key) . ' = VALUES(' . wrapsqlfield($key) . ')';
			}
			$duplicate = "ON DUPLICATE KEY UPDATE " . implode(', ', $duplicateValues);
		}

		$ignore = $ignore?'IGNORE':'';
		$q = "INSERT " . ($ignore ? 'IGNORE' : '') . " INTO {$table} ({$KEYS}) VALUES ({$VALUES}) " . ($ignore ? '' : $duplicate) . ";";
		return $this->Insert($q);
	}

	public function InsertMulti($table, $keys, $data)
	{
		if(!is_array($data) || !is_array($keys) || scount($keys) < 1 || scount($data) < 1 || !cval($table))
			return false;
		$this->doDB();

		$VALUES_ARR = [];
		foreach($data as $values_row)
		{
			$values_row = $this->prepareInsertValues($values_row);
			$VALUES_ARR[] = '(' . $values_row . ')';
		}

		$KEYS = array_map('wrapsqlfield', $keys);
		$KEYS = implode(', ', $KEYS);

		$q = "INSERT IGNORE INTO {$table} ({$KEYS}) VALUES " . implode(', ', $VALUES_ARR) . ";";
		return $this->CQuery($q);
	}

    public function Replace($table, $data)
    {
        if(!is_array($data) || scount($data) < 1 || !cval($table))
            return false;
        $this->doDB();

        $KEYS = implode(', ', array_keys($data));
        $VALUES = $this->prepareInsertValues($data);
        $q = "REPLACE INTO {$table} ({$KEYS}) VALUES ({$VALUES});";
        return $this->Query($q);
    }

	public function SelectIn($table, $keys, $in, $moreWhere = '')
	{
		// TODO: keys 'AS' ability
		// TODO: Fatal log
		if(!cval($table))
			return false;
		if(!is_array($keys) || scount($keys) < 1)
			return false;
		if(!is_array($in) || scount($in) < 1)
			return false;
		if(!is_string($moreWhere))
			return false;
		$ins = [];

		foreach($in as $inKey => $inVals)
		{
			if(scount($inVals) < 1)
				continue;
			$inKey = wrapsqlfield($inKey);
			$inVals = $this->Escape($inVals);
			$inValsString = implode(', ', array_map('wrapsql', $inVals));
			$ins[] = "$inKey IN ($inValsString)";
		}


		if(strlen($moreWhere) > 1)
		{
			$ins[] = $moreWhere;
		}
		$ins = implode(' AND ', $ins);

		$select = implode(', ', array_map('wrapsqlfield', $keys));
		$query = "SELECT $select FROM `$table` WHERE $ins;";
		return $this->CQuery($query);
	}

	public function RemoveJSON($table, $data, $where = false, $field = 'DATA')
	{
		if(!cval($table))
			return false;
		if(!cval($data) || !is_array($data))
			return false;

		$where = cval($where) ? ' WHERE ' . $where : '';
		$query = [];
		foreach($data as $key)
		{
			$query[] = "'$.{$key}'";
		}
		$q = implode(', ', $query);
		$q = "`{$field}` = JSON_REMOVE(`{$field}`, {$q})";
		return $this->Query("UPDATE {$table} SET {$q} {$where};");
	}

	public function UpsertJSON($table, $data, $where = false, $field = 'DATA')
	{
		if(!cval($table))
			return false;
		if(!cval($data) || !is_array($data))
			return false;

		$where = cval($where) ? ' WHERE ' . $where : '';
		$query = [];
		foreach($data as $key => $val)
		{
			$query[] = "'$.{$key}'";
			if(is_object($val))
			    $val = get_object_vars($val);
			if(is_array($val))
			{
				$query[] = "JSON_OBJECT(" . $this->prepareJSONObject($val) . ")";
			}
			elseif(is_null($val))
			{
				$query[] = "NULL";
			}
			elseif(is_num_ex($val))
			{
				$query[] = $val;
			}
			else
			{
				$query[] = "'" . $this->Escape($val) . "'";
			}
		}
		$q = implode(', ', $query);
		$q = "`{$field}` =  JSON_SET(`{$field}`, {$q})";
		$q ="UPDATE {$table} SET {$q} {$where};";
		return $this->Query($q);
	}

	public function GetThreadID()
	{
		if($this->db)
			return mysqli_thread_id($this->db);
		return false;
	}

	public function prepareJSONObject($ar)
	{
		$arQuery = [];
		foreach($ar as $arKey => $arValue)
		{
			$arQuery[] = "'$arKey'";
            if(is_object($arValue))
                $arValue = get_object_vars($arValue);
			if(is_array($arValue))
			{
				$arQuery[] = "JSON_OBJECT(" . $this->prepareJSONObject($arValue) . ")";
			}
			else
			{
				$arQuery[] = "'$arValue'";
			}
		}
		return implode(', ', $arQuery);
	}

	public function JSONAddPaths($structure, $path)
	{
		$create = [];
		$sAr = explod3('.', $path);
		$levels = scount($sAr);
		if($levels == 0)
			return [];
		elseif($levels >= 1)
		{
			$curStructure = $structure;
			$curLevel = 1;
			foreach($sAr as $level)
			{
				if(!array_key_exists($level, $curStructure))
					$create[implode('.', array_slice($sAr, 0, $curLevel))] = $curLevel != $levels ? [] : null;
				$curStructure = reset($curStructure);
				$curLevel++;
			}
		}
		return $create;
	}

	// TODO: MULTIPLY WHERE
	public function Update($table, $data, $where = false)
	{
		if(!cval($table))
			return false;
		$where = cval($where) ? ' WHERE ' . $where : '';

		if(!cval($data) || !is_array($data))
			return false;

		$query = [];
		foreach($data as $key => $val)
		{
            if(is_null($val))
            {
                $query[] = '`' . $key . '` = NULL';
            }
            elseif(strpos($val, 'FROM_UNIXTIME') !== false)
            {
                $query[] = '`' . $key . '` = ' . $val;
            }
            else
            {
                $query[] = '`' . $key . '` = \'' . $val . '\'';
            }
		}
		$q = implode(', ', $query);
		return $this->Query('UPDATE ' . $table . ' SET ' . $q . ' ' . $where . ';');
	}

	// Alias
	public function rows()
	{
		return $this->SelectedRowsCount();
	}

	public function SelectedRowsCount($result = false)
	{
		if(!$result)
		{
			if(!cval($this->lastresult))
				return false;
			return intval(sval($this->lastresult->num_rows, 0));
		}
		else
		{
			if(!cval($result))
				return false;
			return intval(sval($result->num_rows, 0));
		}
	}
	
	public function affected_rows()
    {
        return $this->db->affected_rows;
    }

	public function InsertID()
	{
		return cval($this->db->insert_id);
	}

	public function fetch_assoc()
	{
		return $this->Fetch();
	}

	/**
	 * @param bool $result
	 *
	 * @return mixed
	 */
	public function Fetch($result = false)
	{
		if(!$result)
		{
			if(!cval($this->lastresult))
				return false;
			return $this->lastresult->fetch_assoc();
		}
		else
		{
			if(!cval($result))
				return false;
			return $result->fetch_assoc();
		}
	}

	public function FetchByKey($key = false, $result = false)
	{
		$return = [];
		$operator = cval($result) ?: cval($this->lastresult);
		if(!$operator)
			return false;

		while($row = $operator->fetch_assoc())
		{
			if($key)
				$return[$row[$key]] = $row;
			else
				$return[] = $row;
		}
		return $return;
	}


	public function Close()
	{
        if($this->db !== false && $this->db->ping() )
        {
		    $this->db->close();
            $this->db = false;
        }
	}

	/**
	 * Создаёт стандартные переменные
	 */
	public function setVariables()
	{
		$this->debug = defined('IS_DEV') && IS_DEV ? IS_DEV : false;
	}

	public function debug($debug = true)
	{
		$this->debug = $debug;
	}

	public function setTimeout($timeout = 10)
	{

	}

	/**
	 * Пишет в лог
	 * @param $msg
	 * @param bool $function
	 */
	private function log($msg, $function = false)
	{
		$function = $function ? '[' . $function . ']' : '';
		self::$log[] = $function . $msg;
	}

	/**
	 * Выводит лог
	 * @param bool $hide
	 */
	public function getLog($hide = false)
	{
		$string = implode("\n", self::$log);
//		dumpEx($string,  __CLASS__ . ' log', $hide);
	}

    public function execFile($file,$bg='> /dev/null 2>/dev/null')
    {
        $cmd = 'mysql --user=' . self::$defuser . ' --password=' . self::$defpass . ' --database=' . self::$defdb . ' --default-character-set=utf8' . ' --force < ' .  $file . ' ' . $bg  ;
        exec($cmd);
    }


}