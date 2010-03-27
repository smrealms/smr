<?php
require_once('SmrSessionMySqlDatabase.class.inc');
if(!defined('USING_AJAX'))
	define('USING_AJAX',false);
class SmrSession
{
	const TIME_BEFORE_EXPIRY = 3600;
	protected static $db;

	public static $session_id;
	public static $account_id;
	public static $old_account_id;
	public static $game_id;
	public static $last_accessed;
	public static $fast_forward;
	public static $var;
	public static $commonIDs;
	public static $generate;
	public static $SN;
	public static $lastSN;
	
	protected static $previousAjaxReturns;
	protected static $ajaxReturns=array();
	
	public static $game_type;
	public static $game_speed;

	public static function init()
	{
		self::$db = new SmrSessionMySqlDatabase();
		// now try the cookie
		if (isset($_COOKIE['session_id']))
		{
			self::$session_id = $_COOKIE['session_id'];
		}
		else
		{
			// create a new session id
			self::$session_id = md5 (uniqid (mt_rand()) );
			setcookie('session_id', self::$session_id);
		}

		// try to get current session
		self::$db->query('DELETE FROM active_session WHERE last_accessed < '.(TIME-self::TIME_BEFORE_EXPIRY));
		self::$db->query('SELECT * FROM active_session WHERE session_id = '.self::$db->escapeString(self::$session_id).' LIMIT 1');
		if (self::$db->nextRecord())
		{
			self::$generate = false;
			self::$session_id		= self::$db->getField('session_id');
			self::$account_id		= self::$db->getField('account_id');
			self::$old_account_id	= self::$db->getField('old_account_id');
			self::$game_id			= self::$db->getField('game_id');
			self::$last_accessed	= self::$db->getField('last_accessed');
			self::$var = @unserialize(@gzuncompress(self::$db->getField('session_var')));
			self::$lastSN = self::$db->getField('last_sn');
			self::$previousAjaxReturns = @unserialize(@gzuncompress(self::$db->getField('ajax_returns')));
			if(!is_array(self::$var))
			{
				self::$account_id	= 0;
				self::$old_account_id	= 0;
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
						if(isset($value['CommonID']))
							self::$commonIDs[$value['CommonID']] = $key;
					}
				} unset($value);
			}
		}
		else
		{
			self::$generate = true;
			self::$account_id	= 0;
			self::$old_account_id	= 0;
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
		if(class_exists('SmrMySqlDatabase',false))
			$db = new SmrMySqlDatabase();
	}
	
	public static function update()
	{
		self::$db= new SmrSessionMySqlDatabase();
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
			self::$db->query('UPDATE active_session SET account_id='.self::$account_id.',old_account_id='.(is_numeric(self::$old_account_id)?self::$old_account_id:0).',game_id=' . self::$game_id . (!USING_AJAX ? ',last_accessed=' . TIME : '') . ',session_var=' . self::$db->escapeBinary($compressed)  . '' .
					',last_sn='.self::$db->escapeString(self::$SN).
					' WHERE session_id=' . self::$db->escapeString(self::$session_id) . (USING_AJAX ? ' AND last_sn='.self::$db->escapeString(self::$lastSN) : '') . ' LIMIT 1');
		}
		else
		{
			self::$db->query('DELETE FROM active_session WHERE account_id=' . self::$account_id . ' LIMIT 1');
			self::$db->query('INSERT INTO active_session (session_id, account_id, old_account_id, game_id, last_accessed, session_var) VALUES(' . self::$db->escapeString(self::$session_id) . ',' . self::$account_id . ',' . self::$old_account_id . ',' . self::$game_id . ',' . TIME . ',' . self::$db->escapeBinary($compressed)  . ')');
		}
		$db = new SmrMySqlDatabase();
	}
	
	public static function updateSN()
	{
		self::$db= new SmrSessionMySqlDatabase();
		if(!USING_AJAX)
			self::$db->query('UPDATE active_session SET last_sn='.self::$db->escapeString(self::$SN).
				' WHERE session_id=' . self::$db->escapeString(self::$session_id) . ' LIMIT 1');
		new SmrMySqlDatabase();	
	}

	public static function destroy()
	{
		self::$db= new SmrSessionMySqlDatabase();
		self::$db->query('UPDATE active_session SET account_id=0,old_account_id=0,game_id=0,session_var=\'\',ajax_returns=\'\' WHERE session_id = ' . self::$db->escapeString(self::$session_id) . ' LIMIT 1');
		self::$session_id = '';
		self::$account_id = 0;
		self::$old_account_id = 0;
		self::$game_id = 0;
		$db = new SmrMySqlDatabase();
	}
	
	public static function retrieveVar($sn)
	{
		self::$SN = $sn;
		SmrSession::updateSN();
		if(!isset(self::$var[$sn]) || empty(self::$var[$sn]))
			return false;
		if(isset(self::$var[$sn]['body']) && isset(self::$var[$sn]['CommonID']))
		{
//			if(preg_match('/processing/',self::$var[$sn]['body']))
			unset(self::$commonIDs[self::$var[$sn]['CommonID']]); //Do not store common id for current page
			unset(self::$var[$sn]['CommonID']);
		}
		
		self::$var[$sn]['RemainingPageLoads'] = 1; // Allow refreshing
		self::$var[$sn]['Expires'] = 0; // Allow refreshing forever
		return self::$var[$sn];
	}
	
	public static function resetLink($container, $sn) //Do not allow sharing SN, useful for forwarding.
	{
		if(isset(self::$var[$sn]['CommonID']))
			unset(self::$commonIDs[self::$var[$sn]['CommonID']]); //Do not store common id for reset page, to allow refreshing to always give the same page in response
		self::$SN = $sn;
		if(!isset($container['Expires']))
			$container['Expires'] = 0; // Lasts forever
		if(!isset($container['RemainingPageLoads']))
			$container['RemainingPageLoads'] = 1; // One refresh to destroy the link

		self::$var[$sn] = $container;
		return $sn;
	}
	
	public static function updateVar($key,$value)
	{
		global $var;
		if($value===null)
		{
			unset($var[$key]);
			unset(self::$var[self::$SN][$key]);
		}
		else
		{
			$var[$key] = $value;
			self::$var[self::$SN][$key] = $value;
		}
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
	
	protected static function generateSN(&$container)
	{
		$container['CommonID'] = self::getCommonID($container);
		if(isset(self::$commonIDs[$container['CommonID']]))
			$sn = self::$commonIDs[$container['CommonID']];
		else
			do{$sn = substr(md5(mt_rand()),0,8);} while(isset(self::$var[$sn]));
		self::$commonIDs[$container['CommonID']] = $sn;
		return $sn;
	}
	
	protected static function getCommonID($commonContainer)
	{
		unset($commonContainer['Expires']);
		unset($commonContainer['RemainingPageLoads']);
		unset($commonContainer['CommonID']);
		return md5(serialize($commonContainer));
	}

	public static function get_new_sn($container, $forceSN = false) // Container['ID'] MUST be unique to a specific action, if there will be two different outcomes from containers given the same ID then problems will likely arise.
	{
		return self::addLink($container, $forceSN);
	}

	public static function get_new_href($container,$forceFullURL=false)
	{
		if($forceFullURL===true||stripos($_SERVER['REQUEST_URI'],'loader.php')===false)
			return URL.'/loader.php?sn=' . self::addLink($container);
		else
			return '?sn=' . self::addLink($container);
	}
	
	public static function addAjaxReturns($element,$contents)
	{
		self::$ajaxReturns[$element] = $contents;
		return isset(self::$previousAjaxReturns[$element])&&self::$previousAjaxReturns[$element]==$contents;
	}
	
	public static function saveAjaxReturns()
	{
		if(empty(self::$ajaxReturns))
			return;
		$compressed = gzcompress(serialize(self::$ajaxReturns));
		self::$db->query('UPDATE active_session SET ajax_returns=' . self::$db->escapeBinary($compressed) .
				' WHERE session_id=' . self::$db->escapeString(self::$session_id) . ' LIMIT 1');
	}
}

SmrSession::init();
?>