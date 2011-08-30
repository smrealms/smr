<?php
abstract class AbstractSmrAccount
{
	const USER_RANKINGS_EACH_STAT_POW = .9;
	const USER_RANKINGS_TOTAL_SCORE_POW = .3;
	const USER_RANKINGS_RANK_BOUNDARY = 5.2;
	protected static $USER_RANKINGS_SCORE = array(
		array(array('Trade','Experience','Total'),.1,0.5),
		array(array('Trade','Money','Profit'),0.00005,0.5),
		array(array('Killing','Kills'),1000,1)
//		array(array('','',''),),
		);
	
	protected static $CACHE_ACCOUNTS = array();
	
	protected $db;
	
	var $account_id;
	var $login;
	protected $password;
	var $email;
	var $first_name;
	protected $validated;
	protected $cellPhone;
	var $validation_code;
	var $last_login;
	protected $hofName;
	protected $ircNick;
	protected $veteran;
	protected $logging;
	var $offset;
	var $images;
	protected $fontSize;
	protected $passwordReset;
	protected $points;
	protected $useAJAX;
	protected $mailBanned;
	protected $HOF;
	protected $individualScores;
	protected $score;
	protected $cssLink;
	protected $defaultCSSEnabled;
	protected $messageNotifications;
	protected $centerGalaxyMapOnPlayer;
	protected $oldAccountIDs = array();
	protected $maxRankAchieved;
	protected $referrerID;
	protected $dateShort;
	protected $timeShort;
	protected $template;
	
	protected $npc;
	
	protected $hasChanged;

	public static function &getAccount($accountID,$forceUpdate = false)
	{
		if($forceUpdate || !isset(self::$CACHE_ACCOUNTS[$accountID]))
		{
			self::$CACHE_ACCOUNTS[$accountID] = new SmrAccount($accountID);
		}
		return self::$CACHE_ACCOUNTS[$accountID];
	}
	
	public static function &getAccountByName($login,$forceUpdate = false)
	{
		$db = new SmrMySqlDatabase();
		$db->query('SELECT account_id FROM account WHERE login = '.$db->escapeString($login).' LIMIT 1');
		if($db->nextRecord())
			return self::getAccount($db->getField('account_id'),$forceUpdate);
		$return = null;
		return $return;
	}
	
	public static function &getAccountByHofName($hofName,$forceUpdate = false)
	{
		$db = new SmrMySqlDatabase();
		$db->query('SELECT account_id FROM account WHERE hof_name = '.$db->escapeString($hofName).' LIMIT 1');
		if($db->nextRecord())
			return self::getAccount($db->getField('account_id'),$forceUpdate);
		$return = null;
		return $return;
	}

	public static function &getAccountByIrcNick($nick, $forceUpdate = false)
	{
		$db = new SmrMySqlDatabase();
		$db->query('SELECT account_id FROM account WHERE irc_nick = '.$db->escapeString($nick).' LIMIT 1');
		if($db->nextRecord())
			return self::getAccount($db->getField('account_id'), $forceUpdate);
		$return = null;
		return $return;
	}

	public static function &findAccountByIrcNick($nick, $forceUpdate = false)
	{
		$db = new SmrMySqlDatabase();
		$db->query('SELECT account_id FROM account WHERE irc_nick LIKE '.$db->escapeString($nick).' LIMIT 2');
		if($db->getNumRows() > 1) {
			$return = true;
			return $return;
		} elseif($db->nextRecord()) {
			return self::getAccount($db->getField('account_id'), $forceUpdate);
		} else{
			$return = null;
			return $return;
		}
	}

	public static function &createAccount($login,$password,$email,$first_name,$last_name,$address,$city,$postal_code,$country_code,$icq,$timez,$referral)
	{
		if($referral!=0)
		{
			try
			{
				SmrAccount::getAccount($referral);
			}
			catch(Exception $e)
			{
				throw new Exception('Referral account does not exist: '.$e->getMessage());
			}
		}
		$db = new SmrMySqlDatabase();
		$db->query('INSERT INTO account (login, password, email, first_name, last_name, address, city, postal_code, country_code, icq, validation_code, last_login, offset,referral_id,hof_name) VALUES(' .
			$db->escape_string($login) . ', ' . $db->escape_string(md5($password)) . ', ' . $db->escape_string($email) . ', ' .
			$db->escape_string($first_name) . ', ' . $db->escape_string($last_name) . ', ' .
			$db->escape_string($address) . ', ' . $db->escape_string($city) . ', ' . $db->escape_string($postal_code) . ', ' .
			$db->escape_string($country_code) . ', ' . $db->escape_string(trim($icq)) . ', ' . $db->escape_string(substr(SmrSession::$session_id, 0, 10)) . ',' . TIME . ',' .$db->escapeNumber($timez).','.$db->escapeNumber($referral).','.$db->escapeString($login).')');
		return self::getAccountByName($login);
	}
	
	public static function getUserScoreCaseStatement(&$db)
	{
		$userRankingTypes = array();
		$case = 'FLOOR(SUM(CASE type ';
		foreach(self::$USER_RANKINGS_SCORE as $userRankingScore)
		{
			$userRankingType = $db->escapeArray($userRankingScore[0],false,false,':',false);
			$userRankingTypes[] = $userRankingType;
			$case.= ' WHEN '.$db->escapeString($userRankingType).' THEN POW(amount*'.$userRankingScore[1].','.SmrAccount::USER_RANKINGS_EACH_STAT_POW.')*'.$userRankingScore[2];
		}
		$case .= ' END))';
		return array('CASE'=>$case,'IN'=>$db->escapeArray($userRankingTypes));
	}
	
	function __construct($accountID)
	{
		$this->db = new SmrMySqlDatabase();
		$this->db->query('SELECT * FROM account WHERE account_id = '.$this->db->escapeNumber($accountID).' LIMIT 1');
		
		if ($this->db->nextRecord())
		{
			$row = $this->db->getRow();
			$this->account_id		= $row['account_id'];
			
			$this->login			= $row['login'];
			$this->password			= $row['password'];
			$this->email			= $row['email'];
			$this->first_name		= $row['first_name'];
			$this->validated		= $this->db->getBoolean('validated');
			
			$this->last_login		= $row['last_login'];
			$this->validation_code 	= $row['validation_code'];
			$this->cellPhone        = $row['cell_phone'];
			$this->veteran			= $this->db->getBoolean('veteran');
			$this->logging			= $this->db->getBoolean('logging');
			$this->offset			= $row['offset'];
			$this->images			= $row['images'];
			$this->fontSize			= $row['fontsize'];
			
			$this->passwordReset	= $row['password_reset'];
			$this->useAJAX			= $this->db->getBoolean('use_ajax');
			$this->mailBanned		= (int)$row['mail_banned'];
			
			$this->cssLink			= $row['css_link'];
			$this->defaultCSSEnabled	= $this->db->getBoolean('default_css_enabled');
			$this->centerGalaxyMapOnPlayer	= $this->db->getBoolean('center_galaxy_map_on_player');
			
			$this->messageNotifications = $this->db->getObject('message_notifications');
			
			if(USE_COMPATIBILITY)
			{
				foreach(Globals::getCompatibilityDatabases('Game') as $databaseName => $databaseInfo)
				{
					$this->oldAccountIDs[$databaseName] = $row[$databaseInfo['Column']];
				}
			}
			
			$this->referrerID		= $row['referral_id'];
			$this->maxRankAchieved	= $row['max_rank_achieved'];
			
			$this->hofName			= $row['hof_name'];
			$this->ircNick			= $row['irc_nick'];

			$this->dateShort		= $row['date_short'];
			$this->timeShort		= $row['time_short'];
			
			$this->template			= $row['template'];
			$this->colourScheme		= $row['colour_scheme'];
			
			if(empty($this->hofName))
				$this->hofName=$this->login;
		}
		else
		{
			throw new Exception('This account does not exist!');
		}
	}
	
	public function is_disabled()
	{
		$this->db->query('SELECT * FROM account_is_closed JOIN closing_reason USING(reason_id) ' .
			'WHERE account_id = '.$this->account_id.' LIMIT 1');
		if ($this->db->nextRecord())
		{
			// get the expire time
			$expire_time = $this->db->getField('expires');
			$reason		 = $this->db->getField('reason');
			
			// are we over this time?
			if ($expire_time > 0 && $expire_time < TIME)
			{
				// get rid of the expire entry
				$this->unbanAccount();
				return false;
			}
			return $reason;
		}
		else
			return false;
	}
	
	public function is_sms_blacklisted()
	{
		$this->db->query('SELECT reason FROM account_sms_blacklist ' .
			'WHERE account_id = '.$this->account_id.' LIMIT 1');
		if ($this->db->nextRecord())
			return $this->db->getField('reason');
		else
			return false;
	}

	function is_allowed($permission)
	{
		$this->db->query('SELECT account_id FROM account WHERE account_id = '.$this->account_id.' AND permission LIKE \'%'.$permission.'%\' LIMIT 1');
		return $this->db->getNumRows();
	}
	
	function update()
	{
		$this->db->query('UPDATE account SET email = '.$this->db->escapeString($this->email).', ' .
			'validation_code = '.$this->db->escapeString($this->validation_code).', ' .
			'validated = '.$this->db->escapeBoolean($this->validated).', ' .
			'password = '.$this->db->escapeString($this->password).', ' .
			'images = '.$this->db->escapeString($this->images).', ' .
			'password_reset = '.$this->db->escapeString($this->passwordReset).', ' .
			'use_ajax='.$this->db->escapeBoolean($this->useAJAX).', ' .
			'mail_banned='.$this->db->escapeNumber($this->mailBanned).', ' .
			'max_rank_achieved='.$this->db->escapeNumber($this->maxRankAchieved).', ' .
			'default_css_enabled='.$this->db->escapeBoolean($this->defaultCSSEnabled).', ' .
			'center_galaxy_map_on_player='.$this->db->escapeBoolean($this->centerGalaxyMapOnPlayer).', ' .
			'message_notifications='.$this->db->escapeObject($this->messageNotifications).
			' WHERE account_id = '.$this->account_id.' LIMIT 1');
		$this->hasChanged = false;
	}
	
	function update_ip()
	{
		// try to get a real ip first
		if (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))
			$curr_ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		else
			$curr_ip = $_SERVER['REMOTE_ADDR'];
		
		// more than 50 elements in it?
		
		$this->db->query('SELECT account_id,time,ip FROM account_has_ip WHERE account_id = '.$this->account_id.' ORDER BY time ASC');
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
		list($fi,$se,$th,$fo) = preg_split('/[.\s,]/', $curr_ip, 4);
		if ($curr_ip != 'unknown' && $curr_ip != 'unknown...' && $curr_ip != 'unknown, unknown')
		{
			$curr_ip = $fi.'.'.$se.'.'.$th.'.'.$fo;
			$host = gethostbyaddr($curr_ip);
		} else $host = 'unknown';
		
		// save...first make sure there isn't one for these keys (someone could double click and get error)
		$this->db->query('REPLACE INTO account_has_ip (account_id, time, ip, host) VALUES ('.$this->account_id.', '.TIME.', '.$this->db->escapeString($curr_ip).', '.$this->db->escapeString($host).')');
	}
	
	public function update_last_login()
	{
		$this->last_login = TIME;
		$this->db->query('UPDATE account SET last_login = '.$this->last_login.' ' .
			'WHERE account_id = '.$this->account_id);
	}
	
	public function setLoggingEnabled($bool)
	{
		if($this->logging==$bool)
			return;
		$this->db->query('UPDATE account SET logging = '.$this->db->escapeBoolean($bool).' WHERE account_id = '.$this->getAccountID().' LIMIT 1');
		$this->logging=$bool;
	}
	
	public function isLoggingEnabled()
	{
		return $this->logging;
	}
	
	public function isVeteranBumped()
	{
		return $this->veteran;
	}
	
	public function isVeteran()
	{
		return $this->isVeteranBumped() || $this->get_rank() >= FLEDGLING;
	}
	
	public function isNewbie()
	{
		return !$this->isVeteran();
	}
	
	public function isNPC()
	{
		if(!isset($this->npc))
		{
			$this->db->query('SELECT login FROM npc_logins WHERE login = '.$this->db->escapeString($this->getLogin()).' LIMIT 1;');
			$this->npc = $this->db->nextRecord();
		}
		return $this->npc;
	}
	
	protected function getHOFData()
	{
		if(!isset($this->HOF))
		{
			//Get Player HOF
			$this->db->query('SELECT type,sum(amount) as amount FROM player_hof WHERE account_id=' . $this->getAccountID().' AND game_id IN (SELECT game_id FROM game WHERE ignore_stats = '.$this->db->escapeBoolean(false).') GROUP BY type');
			$this->HOF = array();
			while($this->db->nextRecord())
			{
				$hof =& $this->HOF;
				$typeList = explode(':',$this->db->getField('type'));
				foreach($typeList as $type)
				{
					if(!isset($hof[$type]))
					{
						$hof[$type] = array();
					}
					$hof =& $hof[$type];
				}
				$hof = $this->db->getField('amount');
			}
		}
	}
	
	public function getHOF(array $typeList = null)
	{
		$this->getHOFData();
		if($typeList==null)
			return $this->HOF;
		$hof=$this->HOF;
		foreach($typeList as $type)
		{
			if(!isset($hof[$type]))
				return 0;
			$hof = $hof[$type];
		}
		return $hof;
	}
	
	function get_rank_name()
	{
		$rankings = GLOBALS::getUserRanking();
		if(isset($rankings[$this->get_rank()]))
			return $rankings[$this->get_rank()];
		else
			return end($rankings);
	}
	
	public function getScore()
	{
		if(!isset($this->score))
		{
			$score=0;
			foreach($this->getIndividualScores() as $each)
			{
				$score+=$each['Score'];
			}
			$this->score=round($score);
		}
		return $this->score;
	}
	
	public function &getIndividualScores(SmrPlayer &$player = null)
	{
		$gameID=0;
		if($player!=null)
			$gameID = $player->getGameID();
		if(!isset($this->individualScores[$gameID]))
		{
			$this->individualScores[$gameID] = array();
			foreach(self::$USER_RANKINGS_SCORE as $statScore)
			{
				if($player==null)
					$stat = $this->getHOF($statScore[0]);
				else
					$stat = $player->getHOF($statScore[0]);
				$this->individualScores[$gameID][]=array('Stat'=>$statScore[0],'Score'=>pow($stat*$statScore[1],self::USER_RANKINGS_EACH_STAT_POW)*$statScore[2]);
			}
		}
		return $this->individualScores[$gameID];
	}
	
	public function get_rank()
	{
		$rank = ceil(pow($this->getScore(),self::USER_RANKINGS_TOTAL_SCORE_POW)/self::USER_RANKINGS_RANK_BOUNDARY);
		if($rank<1)
			$rank=1;
		if($rank > $this->maxRankAchieved)
			$this->updateMaxRankAchieved($rank);
		return $rank;
	}
	
	protected function updateMaxRankAchieved($rank)
	{
		if($rank <= $this->maxRankAchieved)
			throw new Exception('Trying to set max rank achieved to a lower value: ' . $rank);
		$delta = $rank-$this->maxRankAchieved;
		if($this->hasReferrer())
		{
			$this->getReferrer()->increaseSmrRewardCredits($delta);
		}
		$this->maxRankAchieved+=$delta;
		$this->hasChanged=true;
		$this->update();
	}
	
	public function getReferrerID()
	{
		return $this->referrerID;
	}
	
	public function hasReferrer()
	{
		return $this->referrerID>0;
	}
	
	public function &getReferrer()
	{
		return SmrAccount::getAccount($this->getReferrerID());
	}
	
	function log($log_type_id, $msg, $sector_id = 0)
	{
		if ($this->isLoggingEnabled())
		{
			if (empty($sector_id))
				$sector_id = 0;
			$this->db->query('INSERT INTO account_has_logs ' .
				'(account_id, microtime, log_type_id, message, sector_id) ' .
				'VALUES('.$this->account_id.', '. $this->db->escapeMicrotime(MICRO_TIME) . ', '.$log_type_id.', ' . $this->db->escapeString($msg) . ', '.$sector_id.')');
		}
	}
	
	protected function getSmrCreditsData()
	{
		if(!isset($this->credits)||!isset($this->rewardCredits))
		{
			$this->credits = 0;
			$this->rewardCredits = 0;
			$this->db->query('SELECT * FROM account_has_credits WHERE account_id = '.$this->getAccountID().' LIMIT 1');
			if ($this->db->nextRecord())
			{
				$this->credits = $this->db->getField('credits_left');
				$this->rewardCredits = $this->db->getField('reward_credits');
			}
		}
	}
	
	public function getTotalSmrCredits()
	{
		return $this->getSmrCredits()+$this->getSmrRewardCredits();
	}
	
	public function decreaseTotalSmrCredits($totalCredits)
	{
		if($totalCredits==0)
			return;
		if($totalCredits<0)
			throw new Exception('You cannot use negative total credits');
		if($totalCredits>$this->getTotalSmrCredits())
			throw new Exception('You do not have that many credits in total to use');
			
		$rewardCredits=$this->rewardCredits;
		$credits=$this->credits;
		$rewardCredits-=$totalCredits;
		if($rewardCredits<0)
		{
			$credits+=$rewardCredits;
			$rewardCredits=0;
		}
		if($this->credits==0&&$this->rewardCredits==0)
			$this->db->query('REPLACE INTO account_has_credits (account_id, credits_left, reward_credits) VALUES('.$this->getAccountID().', '.$credits.','.$rewardCredits.')');
		else
			$this->db->query('UPDATE account_has_credits SET credits_left='.$credits.', reward_credits='.$rewardCredits.' WHERE account_id='.$this->getAccountID().' LIMIT 1');
		$this->credits=$credits;
		$this->rewardCredits=$rewardCredits;
	}
	
	public function getSmrCredits()
	{
		$this->getSmrCreditsData();
		return $this->credits;
	}
	
	public function getSmrRewardCredits()
	{
		$this->getSmrCreditsData();
		return $this->rewardCredits;
	}
	
	public function setSmrCredits($credits)
	{
		if($this->getSmrCredits()==$credits)
			return;
		if($this->credits==0&&$this->rewardCredits==0)
			$this->db->query('REPLACE INTO account_has_credits (account_id, credits_left) VALUES('.$this->getAccountID().', '.$credits.')');
		else
			$this->db->query('UPDATE account_has_credits SET credits_left='.$credits.' WHERE account_id='.$this->getAccountID().' LIMIT 1');
		$this->credits=$credits;
	}
	
	public function increaseSmrCredits($credits)
	{
		if($credits==0)
			return;
		if($credits<0)
			throw new Exception('You cannot gain negative credits');
		$this->setSmrCredits($this->getSmrCredits()+$credits);
	}
	
	public function decreaseSmrCredits($credits)
	{
		if($credits==0)
			return;
		if($credits<0)
			throw new Exception('You cannot use negative credits');
		if($credits>$this->getSmrCredits())
			throw new Exception('You cannot use more credits than you have');
		$this->setSmrCredits($this->getSmrCredits()-$credits);
	}
	
	public function setSmrRewardCredits($credits)
	{
		if($this->getSmrRewardCredits()==$credits)
			return;
		if($this->credits==0&&$this->rewardCredits==0)
			$this->db->query('REPLACE INTO account_has_credits (account_id, reward_credits) VALUES('.$this->getAccountID().', '.$credits.')');
		else
			$this->db->query('UPDATE account_has_credits SET reward_credits='.$credits.' WHERE account_id='.$this->getAccountID().' LIMIT 1');
		$this->rewardCredits=$credits;
	}
	
	public function increaseSmrRewardCredits($credits)
	{
		if($credits==0)
			return;
		if($credits<0)
			throw new Exception('You cannot gain negative reward credits');
		$this->setSmrRewardCredits($this->getSmrRewardCredits()+$credits);
	}
	
	public function sendMessageToBox($boxTypeID, $message)
	{
		// send him the message
		self::doMessageSendingToBox($this->getAccountID(), $boxTypeID, $message);
	}
	
	public static function doMessageSendingToBox($senderID, $boxTypeID, $message, $gameID=0)
	{
		$db = new SmrMySqlDatabase();
		// send him the message
		$db->query('INSERT INTO message_boxes
			(box_type_id,game_id,message_text,
			sender_id,send_time) VALUES (' .
			$boxTypeID . ',' .
			$gameID . ',' .
			$db->escapeString($message) . ',' .
			$senderID . ',' .
			TIME . ')'
		);

//		// give him the message icon
//		$db->query('REPLACE INTO player_has_unread_messages (game_id, account_id, message_type_id) VALUES ' .
//						'('.$gameID.', '.$receiverID.', '.$messageTypeID.')');
	}
	
	public function getAccountID()
	{
		return $this->account_id;
	}
	
	public function getOldAccountIDs()
	{
		return $this->oldAccountIDs;
	}
	
	public function getOldAccountID($dbName)
	{
		return isset($this->oldAccountIDs[$dbName])?$this->oldAccountIDs[$dbName]:0;
	}
	
	public function hasOldAccountID($dbName=false)
	{
		if($dbName===false)
			return count($this->getOldAccountIDs())!=0;
		return $this->getOldAccountID($dbName)!=0;
	}
	
	public function hasAllOldAccountIDs()
	{
		foreach($this->getOldAccountIDs() as $oldAccountID)
		{
			if($oldAccountID==0)
				return false;
		}
		return true;
	}
	
	public function linkAccount($login,$pass)
	{
		$result = false;
		foreach(Globals::getCompatibilityDatabases('Game') as $databaseClassName => $databaseInfo)
		{
			if($this->hasOldAccountID($databaseClassName))
				continue;
			require_once(get_file_loc($databaseClassName.'.class.inc'));
			$db = new $databaseClassName();
			$db->query('SELECT account_id FROM account ' .
					   'WHERE login = '.$db->escapeString($login).' AND ' .
							 'password = '.$db->escapeString($pass));
			if ($db->nextRecord())
			{
				$accountID = $db->getField('account_id');
				$this->db = new SmrMySqlDatabase();
				$this->db->query('SELECT account_id FROM account WHERE '.$databaseInfo['Column'].' = ' . $this->db->escapeNumber($accountID) . ' LIMIT 1');
				if($this->db->nextRecord())
					create_error('Only 1 account can link to a given old account.');
				$credits = 0;
				$db = new $databaseClassName();
				$db->query('SELECT credits_left FROM account_has_credits ' .
						'WHERE account_id = '.$db->escapeNumber($accountID));
				if($db->nextRecord())
				{
					$credits = $db->getField('credits_left');
				}
				
				$db->query('SELECT time, amount FROM account_donated ' .
						'WHERE account_id = '.$db->escapeNumber($accountID));
				$db2 = new SmrMySqlDatabase();
				while($db->nextRecord())
					$db2->query('INSERT INTO account_donated (account_id, time, amount) VALUES ('.$this->getAccountID().', ' . $db->getField('time') . ' , '.$db->getField('amount').')');
				
				
//				$db = new $databaseClassName();
//				$db->query('SELECT permission_id FROM account_has_permission ' .
//						'WHERE account_id = '.$db->escapeNumber($accountID));
//				$db2 = new SmrMySqlDatabase();
//				while($db->nextRecord())
//					$db2->query('INSERT IGNORE INTO account_has_permission (account_id, permission_id) VALUES ('.$this->getAccountID().', ' . $db->getField('permission_id').')');
				
				$this->db->query('UPDATE account SET '.$databaseInfo['Column'].' = ' . $this->db->escapeNumber($accountID) . ' WHERE account_id = '.$this->getAccountID().' LIMIT 1');
				$this->increaseSmrCredits($credits);
				$this->oldAccountIDs[$databaseClassName] = $accountID;
				
				// register session
				SmrSession::$old_account_id = $accountID;
				
				$result = true;
			}
		}
		$db = new SmrMySqlDatabase();
		return $result;
	}
	
	public static function upgradeAccount($login,$pass)
	{
		$db = new SmrMySqlDatabase();
		$db->query('SELECT * FROM account WHERE login = '.$db->escapeString($login).' LIMIT 1');
		if($db->nextRecord())
			return false;
		foreach(Globals::getCompatibilityDatabases('Game') as $databaseClassName => $databaseInfo)
		{
			require_once(get_file_loc($databaseClassName.'.class.inc'));
			$db2 = new $databaseClassName();
			$db2->query('SELECT * FROM account ' .
					   'WHERE login = '.$db->escapeString($login).' AND ' .
							 'password = '.$db->escapeString($pass));
			if ($db2->nextRecord())
			{
				$db = new SmrMySqlDatabase();
				$db->query('SELECT * FROM account WHERE '.$databaseInfo['Column'].' = '.$db->escapeString($db2->getField('account_id')).' LIMIT 1');
				if($db->nextRecord())
				{
					$msg = 'The specified old account is already linked to a 1.6 account.';
					header('Location: '.URL.'/login.php?msg=' . rawurlencode(htmlspecialchars($msg, ENT_QUOTES)));
				}
		
				$db3 = new $databaseClassName();
				$db3->query('SELECT * FROM account_is_closed JOIN closing_reason USING(reason_id) ' .
					'WHERE account_id = '.$db2->getField('account_id').' LIMIT 1');
				if ($db3->nextRecord())
				{
					$expire_time = $db3->getField('expires');
					if(!($expire_time > 0 && $expire_time < TIME))
					{
						SmrSession::$old_account_id = $db2->getField('account_id');
						// save session (incase we forward)
						SmrSession::update();
						if ($db3->getField('reason') == 'Invalid eMail')
						{
							header('Location: '.URL.'/email.php');
							exit;
						}
						else
						{
							header('Location: '.URL.'/disabled.php');
							exit;
						}
					}
				}

				$db = new SmrMySqlDatabase();
				$db->query('INSERT INTO account (login, password, email, first_name, last_name, address, city, postal_code, country_code, icq, validation_code, veteran, last_login, logging, offset,images,fontsize,referral_id,hof_name,validated) VALUES(' .
							$db->escape_string($db2->getField('login')) . ', ' . $db->escape_string(md5($db2->getField('password'))) . ', ' . $db->escape_string($db2->getField('email')) . ', ' .
							$db->escape_string($db2->getField('first_name')) . ', ' . $db->escape_string($db2->getField('last_name')) . ', ' .
							$db->escape_string($db2->getField('address')) . ', ' . $db->escape_string($db2->getField('city')) . ', ' . $db->escape_string($db2->getField('postal_code')) . ', ' .
							$db->escape_string($db2->getField('country_code')) . ', ' . $db->escape_string($db2->getField('icq')) . ', ' . $db->escape_string($db2->getField('validation_code')) . ',' .
							$db->escape_string($db2->getField('veteran')) . ',' . $db->escapeBoolean(false) . ',' . TIME . ',' .$db->escapeNumber($db2->getField('offset')).',' .
							$db->escapeString($db2->getField('images')).','.$db->escapeString($db2->getField('fontsize')+20).','.$db->escapeNumber(0).','.$db->escapeString($db2->getField('login')).','.$db->escapeString($db2->getField('validated')).')');
				
				// creates a new user account object
				$account =& SmrAccount::getAccountByName($db2->getField('login'));
				$account->increaseSmrRewardCredits(2); // Give 2 "reward" credits for joining.
				// insert into the account stats table
				$db->query('INSERT INTO account_has_stats (account_id, HoF_name) VALUES('.$account->getAccountID().', ' . $db->escape_string($account->login) . ')');
				SmrSession::$account_id = $account->getAccountID();

				$account->linkAccount($login,$pass);
				$db = new SmrMySqlDatabase();
				return true;
			}
		}
		$db = new SmrMySqlDatabase();
		return false;
	}
	
	public function getLogin()
	{
		return $this->login;
	}
	
	public function getEmail()
	{
		return $this->email;
	}
	
	public function getOffset()
	{
		return $this->offset;
	}
	
	public function getFontSize()
	{
		return $this->fontSize;
	}
	
	public function setFontSize($size)
	{
		if($this->fontSize==$size)
			return;
		$this->db->query('UPDATE account SET fontsize = ' . $this->db->escapeNumber($size) . ' WHERE account_id = '.$this->getAccountID().' LIMIT 1');
		$this->fontSize=$size;
	}
	
	public function getCssLink()
	{
		return $this->cssLink;
	}
	
	public function setCssLink($link)
	{
		if($this->cssLink==$link)
			return;
		$this->db->query('UPDATE account SET css_link = ' . $this->db->escapeString($link,true,true) . ' WHERE account_id = '.$this->getAccountID().' LIMIT 1');
		$this->cssLink=$link;
	}
	
	public function getTemplate()
	{
		return $this->template;
	}
	
	public function setTemplate($template)
	{
		if($this->template==$template)
			return;
		if(!in_array($template,array_keys(Globals::getAvailableTemplates())))
			throw new Exception('Template not allowed: '.$template);
		$this->db->query('UPDATE account SET template = ' . $this->db->escapeString($template) . ' WHERE account_id = '.$this->getAccountID().' LIMIT 1');
		$this->template = $template;
		$colourSchemes = Globals::getAvailableColourSchemes($template);
		$this->setColourScheme($colourSchemes[0]);
	}
	
	public function getColourScheme()
	{
		return $this->colourScheme;
	}
	
	public function setColourScheme($colourScheme)
	{
		if($this->colourScheme==$colourScheme)
			return;
		if(!in_array($colourScheme,array_keys(Globals::getAvailableColourSchemes($this->getTemplate()))))
			throw new Exception('Colour scheme not allowed: '.$colourScheme);
		$this->db->query('UPDATE account SET colour_scheme = ' . $this->db->escapeString($colourScheme) . ' WHERE account_id = '.$this->getAccountID().' LIMIT 1');
		$this->colourScheme = $colourScheme;
	}
	
	public function getHofName()
	{
		return $this->hofName;
	}
	
	public function setHofName($name)
	{
		if($this->hofName==$name)
			return;
		$this->db->query('UPDATE account SET hof_name = ' . $this->db->escapeString($name) . ' WHERE account_id = '.$this->getAccountID().' LIMIT 1');
		$this->hofName=$name;
	}
	
	public function getIrcNick()
	{
		return $this->ircNick;
	}

	public function setIrcNick($nick)
	{
		if($this->ircNick==$nick)
			return;
		$this->db->query('UPDATE account SET irc_nick = ' . $this->db->escapeString($nick, true, true) . ' WHERE account_id = '.$this->getAccountID().' LIMIT 1');
		$this->ircNick=$nick;
	}

	public function getReferralLink()
	{
		return URL . '/login_create.php?ref='.$this->getAccountID();
	}
	
	public function getShortDateFormat()
	{
		return $this->dateShort;
	}
	
	public function setShortDateFormat($format)
	{
		if($this->dateShort==$format)
			return;
		$this->db->query('UPDATE account SET date_short = ' . $this->db->escapeString($format) . ' WHERE account_id = '.$this->getAccountID().' LIMIT 1');
		$this->dateShort=$format;
	}
	
	public function getShortTimeFormat()
	{
		return $this->timeShort;
	}
	
	public function setShortTimeFormat($format)
	{
		if($this->timeShort==$format)
			return;
		$this->db->query('UPDATE account SET time_short = ' . $this->db->escapeString($format) . ' WHERE account_id = '.$this->getAccountID().' LIMIT 1');
		$this->timeShort=$format;
	}
	
	public function getValidationCode()
	{
		return $this->validation_code;
	}
	
	public function setValidated($bool)
	{
		if($this->validated == $bool)
			return;
		$this->validated=$bool;
		$this->hasChanged=true;
		$this->update();
	}
	
	public function isValidated()
	{
		return $this->validated;
	}
	
	public function getCellPhone()
	{
		return $this->cellPhone;
	}

	public function setCellPhone($cell)
	{
		if($this->cellPhone==$cell)
			return;
		$this->db->query('UPDATE account SET cell_phone = ' . $this->db->escapeString($cell, true, true) . ' WHERE account_id = '.$this->getAccountID().' LIMIT 1');
		$this->cellPhone=$cell;
	}

	protected function getPassword()
	{
		return $this->password;
	}
	
	public function isLoggedIn()
	{
		$this->db->query('SELECT * FROM active_session WHERE account_id = '.$this->db->escapeNumber($this->getAccountID()).' LIMIT 1');
		return $this->db->nextRecord();
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
	
	public function addAuthMethod($loginType,$authKey)
	{
		$this->db->query('SELECT account_id FROM account_auth WHERE login_type='.$this->db->escapeString($loginType).' AND auth_key = '.$this->db->escapeString($authKey).';');
		if($this->db->nextRecord())
		{
			if($this->db->getInt('account_id')!=$this->getAccountID())
			{
				throw new Exception('Another account already uses this form of auth.');
			}
			return true;
		}
		$this->db->query('INSERT INTO account_auth values ('.$this->db->escapeNumber($this->getAccountID()).','.$this->db->escapeString($loginType).','.$this->db->escapeString($authKey).');');
		return true;
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
	
	public function isDisplayShipImages()
	{
		return $this->images == 'Yes';
	}
	
	public function isUseAJAX()
	{
		return $this->useAJAX;
	}
	
	public function setUseAJAX($bool)
	{
		if($this->useAJAX == $bool)
			return;
		$this->useAJAX=$bool;
		$this->hasChanged=true;
		$this->update();
	}
	
	public function isDefaultCSSEnabled()
	{
		return $this->defaultCSSEnabled;
	}
	
	public function setDefaultCSSEnabled($bool)
	{
		if($this->defaultCSSEnabled == $bool)
			return;
		$this->defaultCSSEnabled=$bool;
		$this->hasChanged=true;
		$this->update();
	}
	
	public function isReceivingMessageNotifications($messageTypeID)
	{
		return isset($this->messageNotifications[$messageTypeID])?$this->messageNotifications[$messageTypeID]>0:false;
	}
	
	public function getMessageNotifications($messageTypeID)
	{
		return isset($this->messageNotifications[$messageTypeID])?$this->messageNotifications[$messageTypeID]:0;
	}
	
	public function setMessageNotifications($messageTypeID,$num)
	{
		if($this->getMessageNotifications($messageTypeID) == $num)
			return;
		$this->messageNotifications[$messageTypeID]=$num;
		$this->hasChanged=true;
		$this->update();
	}
	
	public function increaseMessageNotifications($messageTypeID,$num)
	{
		if($num==0)
			return;
		if($num<0)
			throw new Exception('You cannot increase by a negative amount');
		$this->setMessageNotifications($messageTypeID, $this->getMessageNotifications($messageTypeID) + $num);
	}
	
	public function decreaseMessageNotifications($messageTypeID,$num)
	{
		if($num==0)
			return;
		if($num<0)
			throw new Exception('You cannot decrease by a negative amount');
		$this->setMessageNotifications($messageTypeID, $this->getMessageNotifications($messageTypeID) - $num);
	}
	
	public function isCenterGalaxyMapOnPlayer()
	{
		return $this->centerGalaxyMapOnPlayer;
	}
	
	public function setCenterGalaxyMapOnPlayer($bool)
	{
		if($this->centerGalaxyMapOnPlayer == $bool)
			return;
		$this->centerGalaxyMapOnPlayer=$bool;
		$this->hasChanged=true;
		$this->update();
	}
	
	public function getMailBanned()
	{
		return $this->mailBanned;
	}
	
	public function isMailBanned()
	{
		return $this->mailBanned>TIME;
	}
	
	public function setMailBanned($time)
	{
		if($this->mailBanned == $time)
			return;
		$this->mailBanned=$time;
		$this->hasChanged=true;
	}
	
	public function increaseMailBanned($increaseTime)
	{
		$time = max(TIME,$this->getMailBanned());
		$this->setMailBanned($time+$increaseTime);
	}
	
	public function hasPermission($permissionID)
	{
		$this->db->query('SELECT *
					FROM account_has_permission
					WHERE account_id = '.$this->getAccountID().' AND
						  permission_id = '.$permissionID.' LIMIT 1');
		return $this->db->nextRecord();
	}
	
	public function getPoints()
	{
		if(!isset($this->points))
		{
			$this->points=0;
			$this->db->lockTable('account_has_points');
			$this->db->query('SELECT * FROM account_has_points WHERE account_id = '.$this->getAccountID().' LIMIT 1');
			if($this->db->nextRecord())
			{
				$this->points=$this->db->getField('points');
				$lastUpdate = $this->db->getField('last_update');
				//we are gonna check for reducing points...
				if($this->points>0 && $lastUpdate < TIME - (7 * 86400))
				{
					$removePoints=0;
					while($lastUpdate < TIME - (7 * 86400))
					{
						$removePoints++;
						$lastUpdate += (7 * 86400);
					}
					$this->removePoints($removePoints,$lastUpdate);
				}
			}
			$this->db->unlock();
		}
		return $this->points;
	}
	
	public function setPoints($numPoints,$lastUpdate=false)
	{
		$numPoints = max($numPoints,0);
		if($this->getPoints()==$numPoints)
			return;
		if ($this->points==0)
			$this->db->query('INSERT INTO account_has_points (account_id, points, last_update) VALUES ('.$this->getAccountID().', '.$numPoints.', '.($lastUpdate?$lastUpdate:TIME).')');
		else if($numPoints<=0)
			$this->db->query('DELETE FROM account_has_points WHERE account_id = '.$this->getAccountID().' LIMIT 1');
		else
			$this->db->query('UPDATE account_has_points SET points = '.$numPoints.($lastUpdate ? ', last_update = '.TIME : '').' WHERE account_id = '.$this->getAccountID().' LIMIT 1');
		$this->points=$numPoints;
	}
	
	public function removePoints($numPoints,$lastUpdate=false)
	{
		if($numPoints>0)
			$this->setPoints($this->getPoints()-$numPoints,$lastUpdate);
	}
	
	public function addPoints($numPoints,SmrAccount &$admin,$reasonID,$suspicion)
	{
		//do we have points
		$this->setPoints($this->getPoints() + $numPoints,TIME);
		$totalPoints = $this->getPoints();
		if ($totalPoints < 10)
			return false;//leave scripts its only a warning
		elseif ($totalPoints < 20)
			$days = 2;
		elseif ($totalPoints < 30)
			$days = 4;
		elseif ($totalPoints < 50)
			$days = 7;
		elseif ($totalPoints < 75)
			$days = 15 ;
		elseif ($totalPoints < 100)
			$days = 30;
		elseif ($totalPoints < 125)
			$days = 60;
		elseif ($totalPoints < 150)
			$days = 120;
		elseif ($totalPoints < 175)
			$days = 240;
		elseif ($totalPoints < 200)
			$days = 480;
		else
			$days = 0; //Forever/indefinite
		
		if($days==0)
		{
			$expireTime = 0;
		}
		else
		{
			$expireTime = TIME + $days * 86400;
		}
		$this->banAccount($expireTime,$admin,$reasonID,$suspicion);
	
		return $days;
	}
	
	public function banAccount($expireTime,SmrAccount &$admin,$reasonID,$suspicion,$removeExceptions = false)
	{
		$this->db->query('REPLACE INTO account_is_closed ' .
				   '(account_id, reason_id, suspicion, expires) ' .
				   'VALUES('.$this->getAccountID().', '.$reasonID.', '.$this->db->escapeString($suspicion).', '.$expireTime.')');
		$this->db->lockTable('active_session');
		$this->db->query('DELETE FROM active_session ' .
				   'WHERE account_id = '.$this->getAccountID().' LIMIT 1');
		$this->db->unlock();
		
		$this->db->query('INSERT INTO account_has_closing_history ' .
				   '(account_id, time, admin_id, action) ' .
				   'VALUES('.$this->getAccountID().', ' . TIME . ', '.$admin->getAccountID().', \'Closed\')');
		$this->db->query('UPDATE player SET newbie_turns = 1 ' .
				   'WHERE account_id = '.$this->getAccountID().' AND ' .
						 'newbie_turns = 0 AND ' .
						 'land_on_planet = \'FALSE\'');
		
		$this->db->query('SELECT game.game_id as game_id FROM game, player ' .
					'WHERE game.game_id = player.game_id AND ' .
						  'account_id = '.$this->getAccountID().' AND ' .
						  'end_date >= ' . TIME);
		while ($this->db->nextRecord())
		{
			$player =& SmrPlayer::getPlayer($this->getAccountID(), $this->db->getField('game_id'));
			$player->updateTurns();
			$player->update();
		}
		$this->log(13, 'Account closed by ' . $admin->getLogin() . '.');
		if($removeExceptions!==false)
			$this->db->query('DELETE FROM account_exceptions WHERE account_id = '.$this->getAccountID());
	}
	
	public function unbanAccount(SmrAccount &$admin = null,$currException=false)
	{
		$adminID = 0;
		if($admin!==null)
			$adminID = $admin->getAccountID();
		$this->db->query('DELETE FROM account_is_closed
					 	WHERE account_id = '.$this->getAccountID().' LIMIT 1');
		$this->db->query('INSERT INTO account_has_closing_history
						(account_id, time, admin_id, action)
						VALUES('.$this->getAccountID().', ' . TIME . ', '.$adminID.', \'Opened\')');
   		$this->db->query('UPDATE player SET last_turn_update = GREATEST(' . TIME . ', last_turn_update)
				   		WHERE account_id = '.$this->getAccountID());
		if($admin!==null)
			$this->log(13, 'Account reopened by ' . $admin->getLogin() . '.');
		else
			$this->log(13, 'Account automatically reopened.');
		if($currException!==false)
	        $this->db->query('REPLACE INTO account_exceptions (account_id, reason) ' .
                        'VALUES ('.$this->getAccountID().', '.$this->db->escapeString($currException).')');
	}
	
	public function getToggleAJAXHREF()
	{
		global $var;
		return SmrSession::get_new_href(create_container('skeleton.php','toggle_processing.php',array('toggle'=>'AJAX','referrer'=>$var['body'])));
	}
	
	public function getUserRankingHREF()
	{
		return SmrSession::get_new_href(create_container('skeleton.php','rankings_view.php'));
	}
	
	public function getPersonalHofHREF()
	{
		return SmrSession::get_new_href(create_container('skeleton.php','hall_of_fame_player_detail.php',array('account_id' => $this->getAccountID())));
	}
}

?>