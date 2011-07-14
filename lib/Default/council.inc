<?php
require_once(get_file_loc('SmrPlayer.class.inc'));
class Council
{
	protected static $RACE_COUNCIL = array();
	protected static $RACE_PRESIDENTS = array();
	protected static $db = null;
	
	private function __construct()
	{
	}
	
	protected static function initialiseDatabase()
	{
		if(self::$db==null)
			self::$db = new SmrMySqlDatabase();
	}
	
	protected static function &getRaceCouncil($gameID,$raceID)
	{
		if(!isset(self::$RACE_COUNCIL[$gameID]))
		{
			self::$RACE_COUNCIL[$gameID] = array();
			self::$RACE_PRESIDENTS[$gameID] = array();
		}
		
		if(!isset(self::$RACE_COUNCIL[$gameID][$raceID]))
		{
			self::initialiseDatabase();
			self::$RACE_COUNCIL[$gameID][$raceID] = array();
			self::$RACE_PRESIDENTS[$gameID][$raceID] = false;
			
			$i=0;
			self::$db->query('SELECT account_id,alignment FROM player ' .
						'WHERE game_id = '.$gameID.' AND ' .
							'race_id = '.$raceID.' ' .
						'ORDER by experience DESC ' .
						'LIMIT '.MAX_COUNCIL_MEMBERS);
			while(self::$db->nextRecord())
			{
				self::$RACE_COUNCIL[$gameID][$raceID][$i] = self::$db->getField('account_id');
				if(self::$RACE_PRESIDENTS[$gameID][$raceID]==false&&self::$db->getField('alignment')>=150)
					self::$RACE_PRESIDENTS[$gameID][$raceID] = self::$db->getField('account_id');
				$i++;
			}
		}
		return self::$RACE_COUNCIL[$gameID][$raceID];
	}
	
	public static function &getPresident($gameID,$raceID)
	{
		self::getRaceCouncil($gameID,$raceID);
		if(isset(self::$RACE_PRESIDENTS[$gameID][$raceID])&&self::$RACE_PRESIDENTS[$gameID][$raceID]!==false&&is_numeric(self::$RACE_PRESIDENTS[$gameID][$raceID]))
		{
			self::$RACE_PRESIDENTS[$gameID][$raceID] =& SmrPlayer::getPlayer(self::$RACE_PRESIDENTS[$gameID][$raceID],$gameID);
		}
		return self::$RACE_PRESIDENTS[$gameID][$raceID];
	}
	
	public static function isOnCouncil($gameID,$raceID,$accountID)
	{
		return in_array($accountID,self::getRaceCouncil($gameID,$raceID));
	}
}

function modifyRelations($race_id_1)
{
	global $player, $db;

	$endtime = mktime(0, 0, 0, date('m'), date('d'), date('Y'));

	$db2 = new SmrMySqlDatabase();

	$db->query('SELECT * FROM player_votes_relation ' .
			   'WHERE time < '.$endtime.' AND ' .
					 'game_id = '.$player->getGameID().' AND ' .
					 'race_id_1 = '.$race_id_1);
	while ($db->nextRecord())
	{
		$account_id	= $db->getField('account_id');
		$race_id_2	= $db->getField('race_id_2');
		$action		= $db->getField('action');

		if ($action == 'INC')
			$relation_modifier = 10;
		else
			$relation_modifier = -10;

		$db2->query('SELECT * FROM race_has_relation ' .
					'WHERE race_id_1 = '.$race_id_1.' AND ' .
						  'race_id_2 = '.$race_id_2.' AND ' .
						  'game_id = '.$player->getGameID());
		if ($db2->nextRecord())
			$relation = $db2->getField('relation') + $relation_modifier;

		if ($relation < -500)
			$relation = -500;

		if ($relation > 500)
			$relation = 500;

		$db2->query('UPDATE race_has_relation ' .
					'SET relation = '.$relation.' ' .
					'WHERE race_id_1 = '.$race_id_1.' AND ' .
						  'race_id_2 = '.$race_id_2.' AND ' .
						  'game_id = '.$player->getGameID());

		$db2->query('DELETE FROM player_votes_relation ' .
					'WHERE account_id = '.$account_id.' AND ' .
						  'game_id = '.$player->getGameID());

	}

}

function checkPacts($race_id_1)
{
	global $player, $db;

	$db2 = new SmrMySqlDatabase();

	$db->query('SELECT * FROM race_has_voting ' .
			   'WHERE end_time < '.TIME.' AND ' .
					 'game_id = '.$player->getGameID().' AND ' .
					 'race_id_1 = '.$race_id_1);
	while ($db->nextRecord())
	{
		$race_id_2	= $db->getField('race_id_2');
		$type		= $db->getField('type');

		// get 'yes' votes
		$db2->query('SELECT * FROM player_votes_pact ' .
					'WHERE game_id = '.$player->getGameID().' AND ' .
						  'race_id_1 = '.$race_id_1.' AND ' .
						  'race_id_2 = '.$race_id_2.' AND ' .
						  'vote = \'YES\'');
		$yes_votes = $db2->getNumRows();

		// get 'no' votes
		$db2->query('SELECT * FROM player_votes_pact ' .
					'WHERE game_id = '.$player->getGameID().' AND ' .
						  'race_id_1 = '.$race_id_1.' AND ' .
						  'race_id_2 = '.$race_id_2.' AND ' .
						  'vote = \'NO\'');
		$no_votes = $db2->getNumRows();

		
		// more yes than no?
		if ($yes_votes > $no_votes)
		{
			if ($type == 'WAR')
			{
				$currentlyParkedAccountIDs = array();
				$raceFedSectors = array(
										$race_id_1 => SmrSector::getLocationSectors($player->getGameID(), LOCATION_GROUP_RACIAL_BEACONS + $race_id_1),
										$race_id_2 => SmrSector::getLocationSectors($player->getGameID(), LOCATION_GROUP_RACIAL_BEACONS + $race_id_2)
									);
				foreach($raceFedSectors as $raceID => &$fedSectors)
				{
					$otherRaceID = $raceID == $race_id_1 ? $race_id_2 : $race_id_1;
					foreach($fedSectors as &$fedSector)
					{
						$sectorPlayers =& $fedSector->getPlayers();
						foreach($sectorPlayers as &$sectorPlayer)
						{
							if($sectorPlayer->getRaceID() == $otherRaceID && $sectorPlayer->canBeProtectedByRace($raceID))
							{
								$currentlyParkedAccountIDs[$raceID][] = $sectorPlayer->getAccountID();
							}
						}
					}
				}
				
				if( count($currentlyParkedAccountIDs[$race_id_1]) + count($currentlyParkedAccountIDs[$race_id_2]) > 0 )
				{
					$expireTime = TIME + TIME_FOR_WAR_VOTE_FED_SAFETY;
					$query = 'REPLACE INTO player_can_fed (account_id, game_id, race_id, expiry, allowed) VALUES ';
					foreach($currentlyParkedAccountIDs as $raceID => &$accountIDs)
					{
						if($raceID == $race_id_1)
							$message = 'We have declared war upon your race';
						else
							$message = 'Your race has declared war upon us';
						$message .= ', you have '.format_time(TIME_FOR_WAR_VOTE_FED_SAFETY).' to vacate our federal space, after that time you will no longer be protected (unless you have strong personal relations).';
						foreach($accountIDs as &$accountID)
						{
							$query .= '('.$db2->escapeNumber($accountID).','.$db2->escapeNumber($player->getGameID()).','.$db2->escapeNumber($raceID).','.$db2->escapeNumber($expireTime).','.$db2->escapeBoolean(true).').';
							SmrPlayer::sendMessageFromRace($raceID, $player->getGameID(), $accountID, $message, $expireTime);
						}
					}
					$db2->query(substr($query,0,-1));
				}
					
				$db2->query('UPDATE race_has_relation ' .
							'SET relation = -400 ' .
							'WHERE game_id = '.$player->getGameID().' AND ' .
								  '(race_id_1 = '.$race_id_1.' AND race_id_2 = '.$race_id_2.' OR ' .
								  'race_id_1 = '.$race_id_2.' AND race_id_2 = '.$race_id_1.')');
	
				// get news message
				$news = 'The '.Globals::getRaceName($race_id_1).' have declared <span class="red">WAR</span> on the '.Globals::getRaceName($race_id_2);
				$db2->query('INSERT INTO news (game_id, time, news_message) VALUES ' .
							'('.$player->getGameID().', '.TIME.', ' . $db->escape_string($news, false) . ')');
			}
			elseif ($type == 'PEACE')
			{
				// get 'yes' votes
				$db2->query('SELECT * FROM player_votes_pact ' .
							'WHERE game_id = '.$player->getGameID().' AND ' .
								  'race_id_1 = '.$race_id_2.' AND ' .
								  'race_id_2 = '.$race_id_1.' AND ' .
								  'vote = \'YES\'');
				$rev_yes_votes = $db2->getNumRows();
	
				// get 'no' votes
				$db2->query('SELECT * FROM player_votes_pact ' .
							'WHERE game_id = '.$player->getGameID().' AND ' .
								  'race_id_1 = '.$race_id_2.' AND ' .
								  'race_id_2 = '.$race_id_1.' AND ' .
								  'vote = \'NO\'');
				$rev_no_votes = $db2->getNumRows();
	
				// more yes than no?
				if ($rev_yes_votes > $rev_no_votes)
				{
					$db2->query('UPDATE race_has_relation ' .
								'SET relation = 300 ' .
								'WHERE game_id = '.$player->getGameID().' AND ' .
									  '(race_id_1 = '.$race_id_1.' AND race_id_2 = '.$race_id_2.' OR ' .
									  'race_id_1 = '.$race_id_2.' AND race_id_2 = '.$race_id_1.')');
	
					//get news message
					$news = 'The '.Globals::getRaceName($race_id_1).' have signed a <span class="dgreen">PEACE</span> treaty with the '.Globals::getRaceName($race_id_2);
					$db2->query('INSERT INTO news (game_id, time, news_message) VALUES ' .
								'('.$player->getGameID().', '.TIME.', ' . $db->escape_string($news, false) . ')');
				}
			}
		}
		
		// delete vote and user votes
		$db2->query('DELETE FROM race_has_voting ' .
					'WHERE game_id = '.$player->getGameID().' AND ' .
						  'race_id_1 = '.$race_id_1.' AND ' .
						  'race_id_2 = '.$race_id_2);
		$db2->query('DELETE FROM player_votes_pact ' .
					'WHERE game_id = '.$player->getGameID().' AND ' .
						  'race_id_1 = '.$race_id_1.' AND ' .
						  'race_id_2 = '.$race_id_2);
		// delete vote and user votes
		$db2->query('DELETE FROM race_has_voting ' .
					'WHERE game_id = '.$player->getGameID().' AND ' .
						  'race_id_1 = '.$race_id_2.' AND ' .
						  'race_id_2 = '.$race_id_1);
		$db2->query('DELETE FROM player_votes_pact ' .
					'WHERE game_id = '.$player->getGameID().' AND ' .
						  'race_id_1 = '.$race_id_2.' AND ' .
						  'race_id_2 = '.$race_id_1);
	}
}

?>