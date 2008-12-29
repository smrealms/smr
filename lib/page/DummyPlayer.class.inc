<?php
require_once('AbstractSmrPlayer.class.inc');

class DummyPlayer extends AbstractSmrPlayer
{
	public function __construct($gameID=0,$playerName='Dummy',$raceID=1,$experience=1000,$alignment=100,$allianceID=0,$shipTypeID=60)
	{
		parent::__construct();
		$this->accountID				= 0;
		$this->gameID					= (int) $gameID;
		$this->playerName				= (string) $playerName;
		$this->playerID					= 0;
		$this->sectorID					= 0;
		$this->lastSectorID				= 0;
		$this->turns					= 1000;
		$this->originalMaintenance		= $this->turns;
		$this->lastMaintenanceUpdate	= 0;
		$this->newbieTurns				= 0;
		$this->lastShipMod				= 0;
		$this->lastNewsUpdate			= 0;
		$this->stunned					= 0;
		$this->fleed					= false;
		$this->dead						= false;
		$this->startDead				= $this->dead;
		$this->landedOnPlanet			= false;
		$this->kicked					= false;
		$this->lastShieldUpdate			= 0;
		$this->lastActive				= 0;
		$this->lastCPLAction			= 0;
		$this->raceID					= (int) $raceID;
		$this->credits					= 0;
		$this->experience				= (int) $experience;
		$this->alignment				= (int) $alignment;
		$this->governmentHelp			= 0;
		$this->allianceID				= (int) $allianceID;
		$this->shipID					= (int) $shipTypeID;
		$this->kills					= 0;
		$this->deaths					= 0;
		$this->lastPort					= 0;
		$this->bank						= 0;
		$this->zoom						= 0;
		
		$this->stats = array();
		$this->gadgets = array();
		$this->pureRelations = array();
	}
	
	public function getPastMaintBought()
	{
		return 0;
	}
	
	public function getPastMaintCredit()
	{
		return 0;
	}
	
	public function getStatsData()
	{
	}
	
	public function getGadgetsData()
	{
	}
	
	public function getPureRelationsData()
	{
	}
	
	public function &getShip()
	{
		require_once(get_file_loc('DummyShip.class.inc'));
		return DummyShip::getCachedDummyShip($this);
	}
	
	public function cacheDummyPlayer()
	{
		$this->getShip()->cacheDummyShip();
		$cache = serialize(&$this);
		$db = new SmrMySqlDatabase();
		$db->query('REPLACE INTO cached_dummys ' .
					'(type, id, info) ' .
					'VALUES (\'DummyPlayer\', '.$db->escapeString($this->getPlayerName()).', '.$db->escapeString($cache).')');	
		 unserialize(&$cache);
	}
	
	public static function &getCachedDummyPlayer($name)
	{
		$db = new SmrMySqlDatabase();
		$db->query('SELECT info FROM cached_dummys ' .
						  'WHERE type = \'DummyPlayer\' AND ' .
						  		'id = '.$db->escapeString($name).' LIMIT 1');
		if($db->nextRecord())
		{
			$return = unserialize($db->getField('info'));
			return $return;
		}
		else
		{
			$return = new DummyPlayer();
			return $return;
		}
	}
	
	public static function getDummyPlayerNames()
	{
		$db = new SmrMySqlDatabase();
		$db->query('SELECT id FROM cached_dummys ' .
						  'WHERE type = \'DummyPlayer\'');
		$dummyNames = array();
		while($db->nextRecord())
		{
			$dummyNames[] = $db->getField('id');
		}
		return $dummyNames;
	}
}
?>