<?
require_once('SmrMySqlDatabase.class.inc');
class SmrSession
{
	protected static $db;

	public static $session_id;
	public static $account_id;
	public static $game_id;
	public static $last_accessed;
	public static $fast_forward;
	public static $var;
	public static $commonIDs;
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
			else
			{
				foreach(self::$var as $key => &$value)
				{
					if ($value['Expires'] > 0 && $value['Expires'] <= TIME) // Use 0 for infinity
					{
						//This link is no longer valid
						unset(self::$var[$key]);
					}
					else if ($value['RemainingPageLoads'] < 0)
					{
						//This link is no longer valid
						unset(self::$var[$key]);
					}
					else
					{
						--$value['RemainingPageLoads'];
						self::$commonIDs[$value['CommonID']] = $key;
					}
				} unset($value);
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
		foreach(self::$var as $key => &$value)
		{
			if ($value['RemainingPageLoads'] <= 0)
			{
				//This link was valid this load but will not be in the future, removing it now saves database space and data transfer.
				unset(self::$var[$key]);
			}
		} unset($value);
		$compressed = gzcompress(serialize(self::$var));
		if(!self::$generate)
		{
			self::$db->query('UPDATE active_session SET game_id=' . self::$game_id . ',last_accessed=' . time() . ',session_var=' . self::$db->escapeString($compressed)  . ' WHERE session_id=' . self::$db->escapeString(self::$session_id) . ' LIMIT 1');
		}
		else
		{
			self::$db->query('DELETE FROM active_session WHERE account_id=' . self::$account_id . ' LIMIT 1');
			self::$db->query('INSERT INTO active_session (session_id, account_id, game_id, last_accessed, session_var) VALUES(' . self::$db->escapeString(self::$session_id) . ',' . self::$account_id . ',' . self::$game_id . ',' . TIME . ',' . self::$db->escapeString($compressed)  . ')');
		}
	}

	public static function destroy() {

		self::$db->query('DELETE FROM active_session WHERE session_id = ' . self::$db->escapeString(self::$session_id) . ' LIMIT 1');
		self::$session_id = '';
		self::$account_id = 0;
		self::$game_id = 0;
	}
	
	public static function retrieveVar($sn)
	{
		if(!isset(self::$var[$sn]) || empty(self::$var[$sn]))
			return false;
		self::$var[$sn]['RemainingPageLoads'] = 1; // Allow refreshing
		self::$var[$sn]['Expires'] = 0; // Allow refreshing forever
		return self::$var[$sn];
	}
	
	public static function addLink($container, $sn = false) // Container['ID'] MUST be unique to a specific action, if there will be two different outcomes from containers given the same ID then problems will likely arise.
	{
		if(!isset($container['Expires']))
			$container['Expires'] = 0; // Lasts forever
		if(!isset($container['RemainingPageLoads']))
			$container['RemainingPageLoads'] = 1; // One refresh to destroy the link
			
		if($sn === false)
		{
			$sn = self::generateSN($container);
		}
		self::$var[$sn] = $container;
		return $sn;
	}
	
	protected function generateSN(&$container)
	{
		$container['CommonID'] = self::getCommonID($container);
		if(isset(self::$commonIDs[$container['CommonID']]))
			$sn = self::$commonIDs[$container['CommonID']];
		else
			do{$sn = substr(md5(mt_rand()),0,8);} while(isset(self::$var[$sn]));
		self::$commonIDs[$container['CommonID']] = $sn;
		return $sn;
	}
	
	protected function getCommonID($commonContainer)
	{
		unset($commonContainer['Expires']);
		unset($commonContainer['RemainingPageLoads']);
		return md5(serialize($commonContainer));
	}

	public static function get_new_sn($container, $forceSN = false) // Container['ID'] MUST be unique to a specific action, if there will be two different outcomes from containers given the same ID then problems will likely arise.
	{
		return self::addLink($container, $forceSN);
	}

	public static function get_new_href($container)
	{
		return 'loader.php?sn=' . self::addLink($container);
	}

}

SmrSession::init();
?>
