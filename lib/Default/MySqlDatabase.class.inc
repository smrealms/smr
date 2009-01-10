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
				$this->error('Connection failed.');        
			}
		}
		
		if(self::$currDbName != $databaseName) {
			self::$currDbName = $databaseName;
			if(!@mysql_select_db(self::$currDbName, self::$dbConn))
			{
				$this->error('database selection failed.');
			}
		}
	}
	
	public function query($query)
	{
		if(!$this->dbResult = @mysql_query($query, self::$dbConn)) {
			$this->error('SQL query failed (' . $query . ')');
		}
		
//	    if(preg_match('/lock/i',$query))
//	  	  echo $query,'<br />';
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
		return @mysql_insert_id($this->dbResult);
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
				$escapedString .= $this->escape_string($value, $quotes) .',';
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
  
  function next_record() {

    if(!$this->db_res) {
      $this->error('No resource to get record from.');
    }
    
    if($this->db_rec = @mysql_fetch_assoc($this->db_res)) {
      return true;
    }
   
    return false;
    
  }
  
  
  function f($name) {
	  
	  return($this->db_rec[$name]);
	  
  }
  
  function fetch_row() {
	  
	  return($this->db_rec);
	  
  }
  
  function lock($table) {
	  
	  if(!@mysql_query('LOCK TABLES ' . $table . ' WRITE', self::$dbConn)) {
		  $this->error('Unable to lock table');
	  }
	  
  }
  
  function nf() {
	  
	  return @mysql_num_rows($this->db_res);
	  
  }
  
  function insert_id() {
	  
	  return @mysql_insert_id(self::$dbConn);
	  
  }
  
  function p($name) {
	  return $this->f($name);
  }
  
  function np() {
	  return $this->nf();
  }
  
  function free() {
	  
  }
  
  function escape_string($db_str, $transfer_html=false) {
	  
	  if ($transfer_html)
		  return '\'' . mysql_escape_string(htmlentities($db_str)) . '\'';
	  else
		  return '\'' . mysql_escape_string($db_str) . '\'';
	  
  }
}
?>