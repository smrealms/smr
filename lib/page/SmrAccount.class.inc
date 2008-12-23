<?
require_once('SmrMySqlDatabase.class.inc');

class SmrAccount
{
	protected static $CACHE_ACCOUNTS = array();
	
	protected $db;
	
	var $accountID;
	var $login;
	var $password;
	var $email;
	var $kills;
	var $experience_traded;
	var $first_name;
	var $validated;
	var $validation_code;
	var $last_login;
	var $games_joined;
	var $planet_busts;
	var $planet_bust_levels;
	var $port_raids;
	var $port_raid_levels;
	var $sectors_explored;
	var $deaths;
	var $goods_traded;
	var $HoF_name;
//    var $donation;
	var $bounties_claimed;
	var $bounty_amount_claimed;
	var $military_claimed;
	var $bounty_amount_on;
	var $veteran;
	var $logging;
	var $offset;
	var $images;
	var $fontsize;
	var $rank;
	var $rank_name;
	protected $passwordReset;
	
	public static function &getAccount($accountID,$forceUpdate = false)
	{
		if($forceUpdate || !isset(self::$CACHE_ACCOUNTS[$accountID]))
		{
			$a = new SmrAccount($accountID);
			return self::$CACHE_ACCOUNTS[$a->getAccountID()] =& $a;
		}
		return self::$CACHE_ACCOUNTS[$accountID];
	}
	
	public static function &getAccountByName($login,$forceUpdate = false)
	{
		$db = new SmrMySqlDatabase();
		$db->query('SELECT account_id FROM account WHERE login = '.$db->escapeString($login).' LIMIT 1');
		if($db->nextRecord());
			return self::getAccount($db->getField('account_id'),$forceUpdate);
		return null;
	}
	
	function __construct($accountID)
	{
		$this->db = new SmrMySqlDatabase();
		$this->db->query('SELECT * FROM account WHERE account_id = '.$accountID.' LIMIT 1');	
		
		if ($this->db->nextRecord())
		{
			$row = $this->db->getRow();
			$this->account_id		= $row['account_id'];
			
			$this->login			= $row['login'];
			$this->password			= $row['password'];
			$this->email			= $row['email'];
			$this->first_name		= $row['first_name'];
			$this->validated		= $row['validated'];
			
			$this->last_login		= $row['last_login'];
			$this->validation_code 	= $row['validation_code'];
			$this->veteran			= $row['veteran'];
			$this->logging			= $row['logging'];
			$this->offset			= $row['offset'];
			$this->images			= $row['images'];
			$this->fontsize			= $row['fontsize'];
			
			$this->passwordReset	= $row['password_reset'];
			
			$this->db->query('SELECT * FROM account_has_stats WHERE account_id = ' . $this->account_id . ' LIMIT 1');
			
			if ($this->db->nextRecord())
			{
				$row = $this->db->getRow();
				$this->kills			= $row['kills'];
				$this->experience_traded= $row['experience_traded'];
				$this->games_joined		= $row['games_joined'];
				$this->planet_busts		= $row['planet_busts'];
				$this->planet_bust_levels = $row['planet_bust_levels'];
				$this->port_raids		= $row['port_raids'];
				$this->port_raid_levels = $row['port_raid_levels'];
				$this->sectors_explored = $row['sectors_explored'];
				$this->deaths			= $row['deaths'];
				$this->goods_traded		= $row['goods_traded'];
				$this->HoF_name			= $row['HoF_name'];
				//                $this->donation			= $row['donation'];
				$this->bounties_claimed = $row['bounties_claimed'];
				$this->bounty_amount_claimed = $row['bounty_amount_claimed'];
				$this->military_claimed = $row['military_claimed'];
				$this->bounty_amount_on = $row['bounty_amount_on'];
				
				
			}
		}
		else
		{
			
			echo('This account does not exist!');
			exit;
			
		}
	}
	
	function is_disabled() {
		
		$this->db->query('SELECT * FROM account_is_closed NATURAL JOIN closing_reason ' .
			'WHERE account_id = '.$this->account_id.' LIMIT 1');
		if ($this->db->nextRecord()) {
			
			// get the expire time
			$expire_time = $this->db->getField('expires');
			$reason		 = $this->db->getField('reason');
			
			// are we over this time?
			if ($expire_time > 0 && $expire_time < time()) {
				
				// get rid of the expire entry
				$this->db->query('DELETE FROM account_is_closed WHERE account_id = '.$this->account_id);
				
				// log the action
				$this->db->query('INSERT INTO account_has_closing_history ' .
					'(account_id, time, admin_id, action) ' .
					'VALUES('.$this->account_id.', ' . time() . ', 0, \'Opened\')');
				
				return false;
				
			}
			
			return $reason;
			
		} else
			return false;
		
	}
	
	function is_allowed($permission) {
		
		$this->db->query('SELECT account_id FROM account WHERE account_id = '.$this->account_id.' AND permission LIKE \'%'.$permission.'%\' LIMIT 1');
		return $this->db->getNumRows();
	}
	
	function update()
	{
		$this->db->query('UPDATE account SET email = '.$this->db->escapeString($this->email).', ' .
			'validation_code = '.$this->db->escapeString($this->validation_code).', ' .
			'validated = '.$this->db->escapeString($this->validated).', ' .
			'password = '.$this->db->escapeString($this->password).', ' .
			'`images` = '.$this->db->escapeString($this->images).', ' .
			'`password_reset` = '.$this->db->escapeString($this->passwordReset).' ' .
			'WHERE account_id = '.$this->account_id.' LIMIT 1');
		
	}
	
	function update_ip()
		{
		
		// get time
		$time = time();
		
		// try to get a real ip first
		if (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))
			$curr_ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		else
			$curr_ip = $_SERVER['REMOTE_ADDR'];
		
		// more than 50 elements in it?
		
		$this->db->query('SELECT * FROM account_has_ip WHERE account_id = '.$this->account_id.' ORDER BY time ASC');
		if ($this->db->getNumRows() > 50 && $this->db->nextRecord())
		{
			
			$delete_id = $this->db->getField('account_id');
			$delete_time = $this->db->getField('time');
			$delete_ip = $this->db->getField('ip');
			
			$this->db->query('DELETE FROM account_has_ip
				WHERE account_id = '.$delete_id.' AND
				time = '.$delete_time.' AND
				ip = '.$this->db->escapeString($delete_ip));
			
		}
		list($fi,$se,$th,$fo) = split ('[.\s,]', $curr_ip, 4);
		if ($curr_ip != 'unknown' && $curr_ip != 'unknown...' && $curr_ip != 'unknown, unknown')
		{
			$curr_ip = $fi.'.'.$se.'.'.$th.'.'.$fo;
			$host = gethostbyaddr($curr_ip);
		} else $host = 'unknown';
		
		// save...first make sure there isn't one for these keys (someone could double click and get error)
		$this->db->query('SELECT * FROM account_has_ip WHERE account_id = '.$this->account_id.' AND time = '.$time);
		if (!$this->db->getNumRows())
			$this->db->query('INSERT INTO account_has_ip (account_id, time, ip, host) VALUES ('.$this->account_id.', '.$time.', '.$this->db->escapeString($curr_ip).', '.$this->db->escapeString($host).')');
		
		}
	
	function update_last_login()
		{
		
		$this->last_login = time();
		$this->db->query('UPDATE account SET last_login = '.$this->last_login.' ' .
			'WHERE account_id = '.$this->account_id);
		
		}
	
	function is_veteran() {
		
		return $this->veteran;
		
	}
	
	function get_rank_name() {
		
		if(!isset($this->rank)) $this->get_rank();
		return $this->rank_name;  
		
	}
	
	function get_rank() {
		
		if(!isset($this->rank)) {
			$this->db->query('SELECT kills, experience_traded, rankings_id, rankings_name, kills_needed, experience_needed
				FROM `rankings` , `account_has_stats`
				WHERE (account_has_stats.kills >= kills_needed OR account_has_stats.experience_traded >= experience_needed)
				AND account_has_stats.account_id = ' . $this->account_id . ' ORDER BY rankings_id DESC LIMIT 2');
			
			if($this->db->getNumRows() > 0) {
				$this->db->nextRecord();
				$row = $this->db->getRow();
				// If both rankings qualify they get the top row. If not they get the other row
				if($row['kills_needed'] <= $row['kills'] && $row['experience_needed'] <= $row['experience_traded']) {
					$this->rank = $row['rankings_id'];
					$this->rank_name = $row['rankings_name'];
				}
				else {
					$this->db->nextRecord();
					$row = $this->db->getRow();
					$this->rank = $row['rankings_id'];
					$this->rank_name = $row['rankings_name'];
				}
			}
			else {
				$this->rank = 1;
				$this->rank_name = 'Newbie';
			}
			
		}
		
		return $this->rank;
		
	}
	
	function log($log_type_id, $msg, $sector_id = 0) {
		
		if ($this->logging == 'TRUE') {
			
			if (empty($sector_id))
				$sector_id = 0;
			
			//sleep if we already have an entry for this second..so we don't get errors.
			$this->db->query('SELECT * FROM account_has_logs WHERE time = ' . time() . ' AND account_id = '.$this->account_id);
			if ($this->db->nextRecord())
				usleep(500000);
			$this->db->query('SELECT * FROM account_has_logs WHERE time = ' . time() . ' AND account_id = '.$this->account_id);
			if ($this->db->nextRecord())
				usleep(250000);
			$this->db->query('SELECT * FROM account_has_logs WHERE time = ' . time() . ' AND account_id = '.$this->account_id);
			if ($this->db->nextRecord())
				usleep(250000);
			
			$this->db->query('INSERT INTO account_has_logs ' .
				'(account_id, time, log_type_id, message, sector_id) ' .
				'VALUES('.$this->account_id.', '. time() . ', '.$log_type_id.', ' . $db->escapeString($msg) . ', '.$sector_id.')');
			
		}
		
	}
	
	function get_credits() {
		
		$this->db->query('SELECT credits_left FROM account_has_credits WHERE account_id = '.$this->account_id.' LIMIT 1');
		if ($this->db->nextRecord())
			return $this->db->getField('credits_left');
		else
			return 0;
		
	}
	
	function set_credits($credits) {
		
		$this->db->query('REPLACE INTO account_has_credits (account_id, credits_left) VALUES('.$this->account_id.', '.$credits.')');
		
	}
	
	public function getAccountID()
	{
		return $this->accountID;
	}
	
	public function getEmail()
	{
		return $this->email;
	}
	
	protected function getPassword()
	{
		return $this->password;
	}
	
	public function checkPassword($password)
	{
		return $this->getPassword()==$password;
	}
	
	public function setPassword($password)
	{
		$this->password = md5($password);
		$this->generatePasswordReset();
		$this->update();
	}
	
	public function generatePasswordReset()
	{
		$this->setPasswordReset(md5($this->getEmail().$this->getPassword().$this->getPasswordReset()));
	}
	
	public function getPasswordReset()
	{
		return $this->passwordReset;
	}
	
	protected function setPasswordReset($passwordReset)
	{
		$this->passwordReset=$passwordReset;
		$this->update();
	}
}

?>