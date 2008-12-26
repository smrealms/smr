<?php
require_once('Globals.class.inc');

require_once('SmrShip.class.inc');

abstract class AbstractSmrPlayer
{
	protected $db;
	
//	var $race_name;
//	var $level_id;
//	var $level_name;
//	var $alliance_description;
//	var $last_turn_update;
//	var $military_payment;
//	var $last_sector_id;
//	var $controlled;
//	var $old_dead;
//	var $bank;
//	var $newbie_warning;
//	var $relations_global;
//	var $relations_global_rev;
//	var $ignore_global;
//	var $last_news_update;
//	var $display_experience;
//	var $display_level_id;
//	var $display_level_name;
//	var $sector_change;
//	var $game_type;
//	var $game_speed;
//	var $validated;
//	var $out_of_game;
//	var $last_ticker_update;
//	var $ticker;
//    var $safe_exit;
//    var $detected;
	protected $accountID;
	protected $gameID;
	protected $playerName;
	protected $playerID;
	protected $sectorID;
	protected $newbieTurns;
	protected $dead;
	protected $startDead;
	protected $landedOnPlanet;
	protected $lastShieldUpdate;
	protected $lastActive;
	protected $raceID;
	protected $credits;
	protected $alignment;
	protected $experience;
	protected $allianceID;
	protected $shipID;
	protected $kills;
	protected $deaths;
	protected $allianceName;
	protected $goods;
	protected $goodsExpGained;
	protected $gadgets;
	protected $pastMaintBought;
	protected $pastMaintCredit;
	protected $pureRelations;
	protected $relations;
	
	protected $hasChanged=false;
	
	protected function __construct()
	{
	}
	
	public function getAccountID()
	{
		return $this->accountID;
	}
	
	public function getGameID()
	{
		return $this->gameID;
	}
	
	public function getNewbieTurns()
	{
		return $this->newbieTurns;
	}
	
	public function hasNewbieTurns()
	{
		return $this->getNewbieTurns()>0;
	}
	public function setNewbieTurns($newbieTurns)
	{
		if($this->newbieTurns == $newbieTurns)
			return;
		$this->newbieTurns=$newbieTurns;
		$this->hasChanged=true;
//		$this->db->query('UPDATE '.$this->tableSQL.' SET newbie_turns = '.$this->newbieTurns.' WHERE '.$this->SQL.' LIMIT 1');
	}
	
	public function getShipID()
	{
		return $this->shipID;
	}
	
	function setShipID($shipID)
	{
		if($this->shipID == $shipID)
			return;
		$this->shipID=$shipID;
		$this->hasChanged=true;
//		$this->db->query('UPDATE '.$this->tableSQL.' SET ship_type_id = '.$this->shipID.' WHERE '.$this->SQL.' LIMIT 1');
	}
	
	public function getLastActive()
	{
		return $this->lastActive;
	}
	
	public function getLastShieldUpdate()
	{
		return $this->lastShieldUpdate;
	}
	
	function setLastShieldUpdate($time)
	{
		if($this->lastShieldUpdate == $time)
			return;
		$this->lastShieldUpdate=$time;
		$this->hasChanged=true;
//		$this->db->query('UPDATE '.$this->tableSQL.' SET last_shield_update = ' . $time . ' WHERE ' . $this->SQL . ' LIMIT 1');
	}
	
	public function getSectorID()
	{
		return $this->sectorID;
	}
	
	public function isDead()
	{
		return $this->dead;
	}
	
	function hasFederalProtection()
	{
        //PAGE BAD BAD BAD!
		// If there isn't a fed beacon we aren't protected
		$this->db->query('SELECT sector_id FROM location ' .
					 'WHERE location_type_id=201 AND sector_id = '.$this->sectorID.' AND ' .
						   'game_id = '.$this->gameID.' LIMIT 1');
		if (!$this->db->getNumRows())
		{
			return false;
		}	

        $ship =& SmrShip::getShip($this->getGameID(),$this->getAccountID());
        $attackRating = $ship->getAttackRating();
		if ($attackRating > 8)
			return false;
		if ($attackRating == 0)
			return true;
		if ($this->getAlignment() >= 0 && $this->getAlignment() >= ($attackRating - 3) * 150)
			return true;
		if ($this->getAlignment() < 0 && $this->getAlignment() > ($attackRating - 4) * 150)
			return true;
		if ($ship->hasIllegalGoods())
			return false;
		return false;
	}
	
	public function canFight()
	{
		return !$this->hasNewbieTurns() && !$this->isDead() && !$this->hasFederalProtection();
	}
	
	public function getStartDead()
	{
		return $this->startDead;
	}
	
	function setDead($bool)
	{
		if($this->dead == $bool)
			return;
		$this->dead=$bool;
		$this->hasChanged=true;
//		$this->db->query('UPDATE '.$this->tableSQL.' SET dead = '.$this->db->escapeBoolean($bool).' WHERE '.$this->SQL.' LIMIT 1');
	}
	
	public function getKills()
	{
		return $this->kills;
	}
	
	public function increaseKills($kills)
	{
		$this->setKills($this->kills+$kills);
	}
	
	public function setKills($kills)
	{
		if($this->kills == $kills)
			return;
		$this->kills=$kills;
		$this->hasChanged=true;
//		$this->db->query('UPDATE '.$this->tableSQL.' SET kills = ' . $kills .' WHERE '.$this->SQL.' LIMIT 1');
	}
	
	public function getDeaths()
	{
		return $this->deaths;
	}
	
	public function increaseDeaths($deaths)
	{
		$this->setDeaths($this->getDeaths()+$deaths);
	}
	
	public function setDeaths($deaths)
	{
		if($this->deaths == $deaths)
			return;
		$this->deaths = $deaths;
		$this->hasChanged=true;
//		$this->db->query('UPDATE '.$this->tableSQL.' SET deaths = '.$this->deaths.' WHERE '.$this->SQL.' LIMIT 1');
	}
	
	public function getAlignment()
	{
		return $this->alignment;
	}
	
	public function getCredits()
	{
		return $this->credits;
	}
	
	public function getExperience()
	{
		return $this->experience;
	}
	
	public function isLandedOnPlanet()
	{
		return $this->landedOnPlanet;
	}
	
	function setLandedOnPlanet($bool)
	{
		if($this->landedOnPlanet == $bool)
			return;
		$this->landedOnPlanet=$bool;
		$this->hasChanged=true;
//		$this->db->query('UPDATE '.$this->tableSQL.' SET land_on_planet = '.$this->db->escapeBoolean($bool).' WHERE '.$this->SQL.' LIMIT 1');
	}
	
	public abstract function getLevelID();
	
	public function getLevelName()
	{
		$LEVEL_REQUIREMENTS = Globals::getLevelRequirements();
		$RACES = Globals::getRaces();
		if ($this->alignment > 150) $a_name = 'Federation';
		elseif ($this->alignment < -150) $a_name = 'Underground';
		else $a_name = 'Sector';
		$level_name = $LEVEL_REQUIREMENTS[$this->getLevelID()]['Level Name'];
		$level_name = str_replace('Race',$RACES[$this->raceID]['Race Name'],$level_name);
		$level_name = str_replace('Alignment',$a_name,$level_name);
		return $level_name;
	}
	
	public function getPlayerID()
	{
		return $this->playerID;
	}
	
	public function getPlayerName()
	{
		return $this->playerName;
	}
	
	public function getName()
	{
		return $this->playerName;
	}
	
	public function getRaceID()
	{
		return $this->raceID;
	}
	
	public function getRaceName()
	{
		$races = Globals::getRaces();
		return $races[$this->getRaceID()]['Race Name'];
	}
	
	function getColoredRaceName($otherRaceID)
	{
		return get_colored_text($this->getRelation($otherRaceID), $this->getRaceName());
	}
	
	function setRaceID($raceID)
	{
		if($this->raceID == $raceID)
			return;
		$this->raceID=$raceID;
		$this->hasChanged=true;
//		$this->db->query('UPDATE '.$this->tableSQL.' SET race_id = '.$this->raceID.' WHERE ' . $this->SQL . ' LIMIT 1');
	}
	
	public function getAllianceID()
	{
		return $this->allianceID;
	}
	
	public function hasAlliance()
	{
		return $this->getAllianceID()!=0;
	}
	
	function setAllianceID($ID)
	{
		if($this->allianceID == $ID)
			return;
		$this->allianceID=$ID;
		$this->hasChanged=true;
//		$this->db->query('UPDATE '.$this->tableSQL.' SET alliance_id = ' . $this->allianceID . ' WHERE '.$this->SQL.' LIMIT 1');
	}
	
	public function getAllianceName()
	{
		if(!isset($this->allianceName))
		{
			//get alliance info
			if ($this->allianceID > 0)
			{
				$sql = $this->db->query('SELECT alliance_name FROM alliance WHERE alliance_id = ' . $this->getAllianceID() . ' AND game_id = ' . $this->getGameID() . ' LIMIT 1');
				$this->db->nextRecord($sql);
				$this->allianceName = stripslashes($this->db->getField('alliance_name'));
			}
			else $this->allianceName = 'No Alliance';
		}
		return $this->allianceName;
	}
	
	public function getSpanColour()
	{
		return get_colored_name('NA', $this->alignment, 'Game', 'Color');
	}
	
	public abstract function getPastMaintBought();
	public abstract function getPastMaintCredit();
	public function getPastMaint()
	{
		$pastMaint = $this->getPastMaintBought()+$this->getPastMaintCredit();
		if ($pastMaint < 0) return 0;
		return $pastMaint;
	}
	
	public abstract function getStats();
	
	public function getStat($name)
	{
		$stats = $this->getStats();
		return $stats[$name];
	}
	
	public abstract function getGadgets();
	
	public function getGadget($gadgetID)
	{
		if(!is_numeric($gadgetID))
		{
			global $GADGETS;
			$gadgetID = $GADGETS[$gadgetID]['ID'];
		}
		$gadgets = $this->getGadgets();
		if(isset($gadgets[$gadgetID]))
			return $gadgets[$gadgetID];
		return false;
	}
	
	public function isGadgetEquipped($gadgetID)
	{
		$gadget = $this->getGadget($gadgetID);
		if($gadget===false)
			return false;
		return $gadget['Equipped'] > 0 && $gadget['Equipped'] < TIME && ($gadget['Expires'] == 0 || $gadget['Expires'] > TIME) && $gadget['Cooldown'] <= TIME;
	}
	
	protected abstract function getPureRelationsData();
	
	public function getPureRelations()
	{
		$this->getPureRelationsData();
		return $this->pureRelations;
	}
	
	public function getPureRelation($raceID)
	{
		$rels = $this->getPureRelations();
		return $rels[$raceID];
	}
	
	public function getRelations()
	{
		if(!isset($this->relations))
		{
			//get relations
			$RACES = Globals::getRaces();
			$pureRels = $this->getPureRelations(); // make sure they're initialised.
			$stats = $this->getStats(); // make sure they're initialised.
			$this->relations = array();
			foreach ($RACES as $race_id => $race_name)
			{
				$this->relations[$race_id] = round($pureRels[$race_id] + $stats['Relations'] / 2);
			}
		}
		return $this->relations;
	}
	
	public function getRelation($raceID)
	{
		$rels = $this->getRelations();
		return $rels[$raceID];
	}
	
	public function &getShip()
	{
		return SmrShip::getShip($this->getGameID(),$this->getAccountID());
	}
	
	public function getDiminishingTraderAttack()
	{
		return max(0,min(1, 1 - ($this->getPastMaint() / ($this->getShip()->getSpeed() * 25 + 475))));
	}
}
?>