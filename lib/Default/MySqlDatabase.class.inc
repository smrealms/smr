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
				$this->error('Database selection failed.');
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
	
	public function getInsertID()
	{
		return @mysql_insert_id(self::$dbConn);
	}
	
	protected function error($err)
	{
		if(!$this->debug)
		{
			echo 'ERROR: db_mysql_inc - ' . $err;
		} 
		else
		{
			global $account,$var,$player;
			$exception = new Exception();
			$message = 'Login: '.$account->login.EOL.EOL.'-----------'.EOL.EOL.
				'Account ID: '.$account->account_id.EOL.EOL.'-----------'.EOL.EOL.
				'E-Mail: '.$account->email.EOL.EOL.'-----------'.EOL.EOL.
				'Error Message: '.$err.EOL.EOL.'-----------'.EOL.EOL.
				'MySQL Error MSG: '.mysql_error().EOL.EOL.'-----------'.EOL.EOL.
				'Trace MSG: '.$exception->getTraceAsString().EOL.EOL.'-----------'.EOL.EOL.
				'$var: '.var_export($var,true);

			try
			{
				throw new Exception;
				if(is_object($player))
					$player->sendMessageToBox(BOX_BUGS_AUTO, nl2br($message));
				else if(is_object($account))
					$account->sendMessageToBox(BOX_BUGS_AUTO, nl2br($message));
				else
					mail('bugs@smrealms.de',
					 'Automatic Bug Report',
					 $message,
					 'From: '.$account->email);
			}
			catch(Exception $e)
			{
				mail('bugs@smrealms.de',
				 'Automatic Bug Report',
				 $message,
				 'From: '.$account->email);
			}
			header('location: ' . URL . '/error.php?msg=Database Error');
		}
		
		exit;
		
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
			if($autoQuotes)
				return $this->escapeArray($escape);
			else
				return $this->escapeArray($escape,$quotes);
		}
		if(is_object($escape))
		{
			if($autoQuotes)
				return $this->escapeArray($escape);
			else
				return $this->escapeArray($escape,$quotes);
		}
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
//		$string = htmlentities($string);
		if($quotes)
			return '\'' . mysql_real_escape_string($string) . '\'';
		return mysql_real_escape_string($string);
	}
	
	public function escapeArray(array $array,$quotes=true,$implodeString=',',$escapeIndividually=true)
	{
		$string = '';
		if($escapeIndividually)
		{
			foreach($array as $value)
			{
				if(is_array($value))
					$string .= $this->escapeArray($value,$quotes,$implodeString,$escapeIndividually).$implodeString;
				else
					$string .= $this->escape($value, $quotes).$implodeString;
			}
			$string = substr($string,0,-1);
		}
		else
		{
			$string = implode($implodeString,$array);
		}
		if(!$escapeIndividually)
		{
			$string = mysql_real_escape_string($string);
			if($quotes)
				$string = '\'' . $string . '\'';
		}
		return $string;
	}
	
	public function escapeNumber($bool,$quotes=false)
	{
		if($bool===true)
			return $this->escapeString('TRUE',$quotes);
		else if($bool===false)
			return $this->escapeString('FALSE',$quotes);
		else
			throw new Exception('Not a boolean!'); //TEST
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
	
	public function escapeObject($object,$quotes=true)
	{
		$string = mysql_real_escape_string(serialize($object));
		if($quotes)
			$string = '\'' . $string . '\'';
		return $string;
	}
	
	
	
	
	
	
	
	
	
	
	
	//below this is purely for compatibility reasons
	
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