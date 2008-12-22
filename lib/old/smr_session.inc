<?

// include file with database class
require_once(LIB . 'global/smr_db.inc');

class SMR_SESSION extends SMR_DB {

	var $session_id;
	var $account_id;
	var $game_id;
	var $last_accessed;
	var $fast_forward;
	var $var;
	var $generate;

	function SMR_SESSION() {

		// now try the cookie
		if (isset($_COOKIE['session_id'])) {
			$this->session_id = $_COOKIE['session_id'];
		}
		else {

			// create a new session id
			$this->session_id = md5 (uniqid (rand()) );
			setcookie('session_id', $this->session_id);
		}

		// try to get current session
		$this->query('SELECT * FROM active_session WHERE session_id = '.$this->escapeString($this->session_id).' LIMIT 1');
		if ($this->next_record()) {
			$this->generate = false;
			$this->session_id		= $this->f('session_id');
			$this->account_id		= $this->f('account_id');
			$this->game_id			= $this->f('game_id');
			$this->last_accessed	= $this->f('last_accessed');
			$this->var = @unserialize(@gzuncompress($this->f('session_var')));
			if(!is_array($this->var)){
				$this->account_id	= 0;
				$this->game_id		= 0;
				$this->var			= array();
			}
		} else {
			$this->generate = true;
			$this->account_id	= 0;
			$this->game_id		= 0;
			$this->var			= array();
		}
		
		if($this->game_id) {
			$this->query('SELECT game_type,game_speed FROM game WHERE game_id = '.$this->game_id.' LIMIT 1');
			if ($this->next_record()) {
				$this->game_type = $this->f('game_type');
				$this->game_speed = $this->f('game_speed');
			}
		}
	}
	
	function update() {
		$compressed = mysql_real_escape_string(gzcompress(serialize($this->var)));
		if(!$this->generate) {
			$this->query('UPDATE active_session SET game_id=' . $this->game_id . ',last_accessed=' . time() . ',session_var=\'' . $compressed  . '\' WHERE session_id=\'' . $this->session_id . '\'');
		}
		else {
			$this->query('DELETE FROM active_session WHERE account_id=' . $this->account_id . ' LIMIT 1');
			$this->query('INSERT INTO active_session (session_id, account_id, game_id, last_accessed, session_var) VALUES(\'' . $this->session_id . '\',' . $this->account_id . ',' . $this->game_id . ',' . time() . ',\'' . $compressed  . '\')');
		}
	}

	function destroy() {

		$this->query('DELETE FROM active_session WHERE session_id = \'' . $this->session_id . '\' LIMIT 1');
		$this->session_id = '';
		$this->account_id = 0;
		$this->game_id = 0;

	}

	function get_new_sn($container) {
		// 8 chars is more than we need to avoid clashes
		do{$sn = substr(md5(mt_rand()),0,8);} while(isset($this->var[$sn]));
		$this->var[$sn] = $container;
		return $sn;
	}

	function get_new_href($container)
	{
		return 'loader.php?sn=' . $this->get_new_sn($container);
	}

}

?>
