<?php
require_once('AbstractSmrPlayer.class.inc');

class DummyPlayer extends AbstractSmrPlayer
{

    function __construct($gameID,$playerName,$raceID,$experience,$alignment,$allianceID,$shipTypeID)
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
    }
    
    public function getPastMaintBought()
    {
    	
    }
    
    public function getPastMaintCredit()
    {
    	
    }
    
    public function getStatsData()
    {
    	
    }
}
?>