<?php
require_once('Database.interface.inc');
abstract class MySqlDatabase implements Database
{
	protected static $errorAlreadyTriggered=false;
	protected static $dbConn;
	protected static $currDbName;
	protected $dbResult = null;
	protected $dbRecord = null;
	protected $debug = true;
	
	public function __construct($host, $user, $password, $databaseName)
	{
		if(!self::$dbConn)
		{
			if(!self::$dbConn = @mysql_connect($host, $user, $password)) {
				$this->error('Connection failed.');
			}
		}
		
		if(self::$currDbName != $databaseName) {
			self::$currDbName = $databaseName;
			if(!@mysql_select_db(self::$currDbName, self::$dbConn))
			{
				$this->error('Database selection failed.');
			}
		}
//		mysql_query("SET NAMES 'utf8' COLLATE 'utf8_general_ci'", self::$dbConn);
	}
	
	public function query($query)
	{
		if(!$this->dbResult = @mysql_query($query, self::$dbConn)) {
			$this->error('SQL query failed (' . $query . ')');
		}
	}
	
	public function nextRecord()
	{
		if(!$this->dbResult)
			$this->error('No resource to get record from.');
		
		if($this->dbRecord = @mysql_fetch_assoc($this->dbResult))
			return true;
		return false;
	}
	
	
	
	public function getField($name)
	{
		return $this->dbRecord[$name];
	}
	
	public function getBoolean($name)
	{
		if($this->dbRecord[$name] == 'TRUE')
			return true;
//		if($this->dbRecord[$name] == 'FALSE')
		return false;
//		throw new Exception('Field is not a boolean');
	}
	
	public function getInt($name)
	{
		return (int)$this->dbRecord[$name];
	}
	
	public function getReal($name)
	{
		return (float)$this->dbRecord[$name];
	}
	
	public function getObject($name,$compressed=false)
	{
		$object = $this->getField($name);
		if($compressed===true)
			$object = gzuncompress($object);
		return unserialize($object);
	}
	
	public function getRow()
	{
		return $this->dbRecord;
	}
	
	public function lockTable($table)
	{
		if(!@mysql_query('LOCK TABLES ' . $table . ' WRITE', self::$dbConn))
		{
			$this->error('Unable to lock table: '. $table);
		}
	}
	
	public function unlock()
	{
		if(!@mysql_query('UNLOCK TABLES', self::$dbConn))
		{
			$this->error('Unable to remove table locks.');
		}
	}
	
	public function getNumRows()
	{
		return @mysql_num_rows($this->dbResult);
	}
	
	public function getChangedRows()
	{
		return @mysql_affected_rows(self::$dbConn);
	}
	
	public function getInsertID()
	{
		return @mysql_insert_id(self::$dbConn);
	}
	
	protected function error($err)
	{
		throw new Exception($err);
	}
	
	public function escape($escape,$autoQuotes=true,$quotes=true)
	{
		if(is_bool($escape))
		{
			if($autoQuotes)
				return $this->escapeBoolean($escape);
			else
				return $this->escapeBoolean($escape,$quotes);
		}
		if(is_numeric($escape))
		{
			if($autoQuotes)
				return $this->escapeNumber($escape);
			else
				return $this->escapeNumber($escape,$quotes);
		}
		if(is_string($escape))
		{
			if($autoQuotes)
				return $this->escapeString($escape);
			else
				return $this->escapeString($escape,$quotes);
		}
		if(is_array($escape))
		{
			return $this->escapeArray($escape,$autoQuotes,$quotes);
		}
		if(is_object($escape))
		{
			if($autoQuotes)
				return $this->escapeObject($escape);
			else
				return $this->escapeObject($escape,$quotes);
		}
	}
	
	public function escapeString($string,$quotes=true,$nullable=false)
	{
		if($nullable===true && ($string===null || $string===''))
			return 'NULL';
		if($string===true)
			$string = 'TRUE';
		else if($string===false)
			$string = 'FALSE';
		if(is_array($string))
		{
			$escapedString = '';
			foreach($string as $value)
			{
				$escapedString .= $this->escapeString($value, $quotes) .',';
			}
			return substr($escapedString,0,-1);
		}
		if($quotes)
			return '\'' . mysql_real_escape_string($string,self::$dbConn) . '\'';
		return mysql_real_escape_string($string,self::$dbConn);
	}
	
	public function escapeBinary($binary)
	{
		return '0x'.bin2hex($binary);
	}
	
	public function escapeArray(array $array,$autoQuotes=true,$quotes=true,$implodeString=',',$escapeIndividually=true)
	{
		$string = '';
		if($escapeIndividually)
		{
			foreach($array as $value)
			{
				if(is_array($value))
					$string .= $this->escapeArray($value,$autoQuotes,$quotes,$implodeString,$escapeIndividually).$implodeString;
				else
					$string .= $this->escape($value, $autoQuotes, $quotes).$implodeString;
			}
			$string = substr($string,0,-1);
		}
		else
		{
			$string = $this->escape(implode($implodeString,$array), $autoQuotes, $quotes);
		}
		return $string;
	}
	
	public function escapeNumber($num,$quotes=false)
	{
		if(is_numeric($num))
			return $this->escapeString($num,$quotes);
		else
			throw new Exception('Not a number! ('.$num.')');
	}
	
	public function escapeBoolean($bool,$quotes=true)
	{
		if($bool===true)
			return $this->escapeString('TRUE',$quotes);
		else if($bool===false)
			return $this->escapeString('FALSE',$quotes);
		else
			throw new Exception('Not a boolean!'); //TEST
	}
	
	public function escapeObject($object,$compress=false,$quotes=true,$nullable=false)
	{
		if($compress===true)
			return $this->escapeBinary(gzcompress(serialize($object)));
		return $this->escapeString(serialize($object),$quotes,$nullable);
	}
	
	
	
	
	
	
	
	
	
	
	
	//below this is purely for compatibility reasons
	
	function free()
	{
		unset($this->dbRecord);
		mysql_free_result($this->dbResult);
	}
	
	function escape_string($string, $transfer_html=false)
	{
		
		if(is_array($string))
		{
			$escapedString = '';
			foreach($string as $value)
			{
				$escapedString .= $this->escape_string($value, $transfer_html) .',';
			}
			return substr($escapedString,0,-1);
		}
		
		if ($transfer_html)
			return '\'' . mysql_real_escape_string(htmlentities($string,ENT_COMPAT,'utf-8'),self::$dbConn) . '\'';
		else
			return '\'' . mysql_real_escape_string($string,self::$dbConn) . '\'';
	}
	
	//below is for Classic compatibility
	
	function next_record() { return $this->nextRecord(); }
	
	function f($name) { return $this->getField($name); }
	
	function fetch_row() { return $this->getRow(); }
	
	function lock($table) { $this->lockTable($table); }
	
	function nf() { return $this->getNumRows(); }
	
	function insert_id() { return $this->getInsertID(); }
	
	function p($name) { return $this->getField($name); }
	
	function np() { return $this->getNumRows(); }
}
?>