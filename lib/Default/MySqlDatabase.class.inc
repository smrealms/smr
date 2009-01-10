<?php
require_once('Database.interface.inc');
abstract class MySqlDatabase implements Database
{
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
			echo 'ERROR: db_mysql_inc - Connection failed.';
//				$this->error('Connection failed.');
			}
		}
		
		if(self::$currDbName != $databaseName) {
			self::$currDbName = $databaseName;
			if(!@mysql_select_db(self::$currDbName, self::$dbConn))
			{
			echo 'ERROR: db_mysql_inc - database selection failed.';
//				$this->error('database selection failed.');
			}
		}
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
		
		if($this->dbRecord = mysql_fetch_assoc($this->dbResult))
			return true;
		return false;
	}
	
	
	
	public function getField($name)
	{
		return $this->dbRecord[$name];
	}
	
	public function getRow()
	{
		return $this->dbRecord;
	}
	
	public function lockTable($table)
	{
		if(!@mysql_query('LOCK TABLES ' . $table . ' WRITE', self::$dbConn))
		{
			$this->error('Unable to lock table');
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
	
	public function getInsertID()
	{
		return @mysql_insert_id(self::$dbConn);
	}
	
	protected function error($err)
	{
		global $URL;
		
		echo mysql_error();
		throw new Exception($err);
		if(!$this->debug)
		{
			echo 'ERROR: db_mysql_inc - ' . $err;
		} 
		else {
			header('location: ' . $URL . '/error.php?msg=Database Error');
		}
		
		exit;
		
	}
	
	public function escapeString($string,$quotes=true)
	{
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
			return '\'' . mysql_real_escape_string($string) . '\'';
		return mysql_real_escape_string($string);
	}
	
	public function escapeBoolean($bool)
	{
		if($bool===true)
			return $this->escapeString('TRUE');
		else if($bool===false)
			return $this->escapeString('FALSE');
		else
			throw new Exception('Not a boolean!'); //TEST
	}
	
	
	
	
	
	
	
	
	
	
	
	//below this is purely for compatibility reasons
	
	function next_record() { return $this->nextRecord(); }
	
	
	function f($name) { return $this->getField($name); }
	
	function fetch_row() { return $this->getRow(); }
	
	function lock($table) { $this->lockTable($table); }
	
	function nf() { return $this->getNumRows(); }
	
	function insert_id() { return $this->getInsertID(); }
	
	function p($name) { return $this->getField(); }
	
	function np() { return $this->getNumRows(); }
	
	function free() { }
	
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
			return '\'' . mysql_escape_string(htmlentities($string)) . '\'';
		else
			return '\'' . mysql_escape_string($string) . '\'';
	}
}
?>