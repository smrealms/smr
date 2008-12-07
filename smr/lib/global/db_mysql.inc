<?php

class DB_Sql {
 
  var $Host     = '';
  var $Database = '';
  var $User     = '';
  var $Password = '';
  var $db_res = null;
  var $db_rec = null;
  var $debug = true;

  function DB_Sql() {

    if(!isset($GLOBALS['db_mysql_conn'])) {
      $GLOBALS['db_mysql_conn'] = false;
      $GLOBALS['db_mysql_name'] = null;
    }

    global $db_mysql_conn, $db_mysql_name;
    
    if(!$db_mysql_conn) {
      if(!$db_mysql_conn = @mysql_connect($this->Host, $this->User, $this->Password)) {
        $this->error('Connection failed.');        
      }
    }
    
    if($db_mysql_name != $this->Database) {
      $db_mysql_name = $this->Database;
      if(!@mysql_select_db($db_mysql_name, $db_mysql_conn)) {
        $this->error('database selection failed.');
      }
    }
       
  }
  
  function query($query) {

    if(!isset($GLOBALS['db_mysql_conn']) || !$GLOBALS['db_mysql_conn']) {
			$this->DB_Sql();
    }

    global $db_mysql_conn, $db_mysql_name;

    if(!$this->db_res = @mysql_query($query, $db_mysql_conn)) {
      $this->error('SQL query failed (' . $query . ')');
    }
    
    //echo $query,'\n\r';
    
  }
  
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
    
    global $db_mysql_conn;
    
    if(!@mysql_query('LOCK TABLES ' . $table . ' WRITE', $db_mysql_conn)) {
      $this->error('Unable to lock table');
    }

  }

  function unlock() {
    
    global $db_mysql_conn;
    
    if(!@mysql_query('UNLOCK TABLES', $db_mysql_conn)) {
      $this->error('Unable to remove table locks.');
    }
    
  }
    
  function nf() {

    return @mysql_num_rows($this->db_res);
      
  }
  
  function insert_id() {
    
    global $db_mysql_conn;
    
    return @mysql_insert_id($db_mysql_conn);
    
  }
  
  function error($err) {
   
    global $URL;
    
   	echo mysql_error();
	throw new Exception($err);
    if(!$this->debug) {
      echo 'ERROR: db_mysql_inc - ' . $err;

    } 
    else {
      header('location: ' . $URL . '/error.php?msg=Database Error');
    }

    exit;
      
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
  
	
	function escapeString($string,$quotes=true) {
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
		elseif($quotes)
			return '\'' . mysql_real_escape_string($string) . '\'';
		else
			return mysql_real_escape_string($string);
	}
}

?>
