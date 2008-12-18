<?
require_once('SmrMySqlDatabase.class.inc');
SmrSession::init();
class SmrSession
{
	protected static $db;

	public static $session_id;
	public static $account_id;
	public static $game_id;
	public static $last_accessed;
	public static $fast_forward;
	public static $var;
	public static $generate;
	
	
	public static $game_type;
	public static $game_speed;

	public static function init()
	{
		self::$db = new SmrMySqlDatabase();
		// now try the cookie
		if (isset($_COOKIE['session_id']))
		{
			self::$session_id = $_COOKIE['session_id'];
		}
		else
		{

			// create a new session id
			self::$session_id = md5 (uniqid (rand()) );
			setcookie('session_id', self::$session_id);
		}

		// try to get current session
		self::$db->query('SELECT * FROM active_session WHERE session_id = '.self::$db->escapeString(self::$session_id).' LIMIT 1');
		if (self::$db->nextRecord())
		{
			self::$generate = false;
			self::$session_id		= self::$db->getField('session_id');
			self::$account_id		= self::$db->getField('account_id');
			self::$game_id			= self::$db->getField('game_id');
			self::$last_accessed	= self::$db->getField('last_accessed');
			self::$var = @unserialize(@gzuncompress(self::$db->getField('session_var')));
			if(!is_array(self::$var))
			{
				self::$account_id	= 0;
				self::$game_id		= 0;
				self::$var			= array();
			}
		}
		else
		{
			self::$generate = true;
			self::$account_id	= 0;
			self::$game_id		= 0;
			self::$var			= array();
		}
		
		if(self::$game_id)
		{
			self::$db->query('SELECT game_type,game_speed FROM game WHERE game_id = '.self::$game_id.' LIMIT 1');
			if (self::$db->nextRecord())
			{
				self::$game_type = self::$db->getField('game_type');
				self::$game_speed = self::$db->getField('game_speed');
			}
		}
	}
	
	public static function update()
	{
		$compressed = mysql_real_escape_string(gzcompress(serialize(self::$var)));
		if(!self::$generate)
		{
			self::$db->query('UPDATE active_session SET game_id=' . self::$game_id . ',last_accessed=' . time() . ',session_var=\'' . $compressed  . '\' WHERE session_id=\'' . self::$session_id . '\'');
		}
		else
		{
			self::$db->query('DELETE FROM active_session WHERE account_id=' . self::$account_id . ' LIMIT 1');
			self::$db->query('INSERT INTO active_session (session_id, account_id, game_id, last_accessed, session_var) VALUES(\'' . self::$session_id . '\',' . self::$account_id . ',' . self::$game_id . ',' . time() . ',\'' . $compressed  . '\')');
		}
	}

	public static function destroy() {

		self::$db->query('DELETE FROM active_session WHERE session_id = \'' . self::$session_id . '\' LIMIT 1');
		self::$session_id = '';
		self::$account_id = 0;
		self::$game_id = 0;

	}

	public static function get_new_sn($container) {
		// 8 chars is more than we need to avoid clashes
		do{$sn = substr(md5(mt_rand()),0,8);} while(isset(self::$var[$sn]));
		self::$var[$sn] = $container;
		return $sn;
	}

	public static function get_new_href($container)
	{
		return 'loader.php?sn=' . self::get_new_sn($container);
	}

}

?>
