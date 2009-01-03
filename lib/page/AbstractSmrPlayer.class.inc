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
	protected $stats;
	protected $pastMaintBought;
	protected $pastMaintCredit;
	protected $pureRelations;
	protected $relations;
	protected $militaryPayment;
	
	protected $HOF;
	
	protected $hasChanged=false;
	protected $hasBountyChanged = array();
	
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
	}
	
	public function getShipTypeID()
	{
		return $this->shipID;
	}
	
	function setShipTypeID($shipID)
	{
		if($this->shipID == $shipID)
			return;
		$this->shipID=$shipID;
		$this->hasChanged=true;
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
		$this->db->query('SELECT sector_id FROM location NATURAL JOIN location_is_fed ' .
					 'WHERE sector_id = '.$this->getSectorID().' AND ' .
						   'game_id = '.$this->getGameID().' LIMIT 1');
		if (!$this->db->getNumRows())
		{
			return false;
		}	

        $ship =& $this->getShip();
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
	}
	
	public function getKills()
	{
		return $this->kills;
	}
	
	public function increaseKills($kills)
	{
		if($kills < 0)
			throw new Exception('Trying to increase negative kills.');
		$this->setKills($this->kills+$kills);
	}
	
	public function setKills($kills)
	{
		if($this->kills == $kills)
			return;
		$this->kills=$kills;
		$this->hasChanged=true;
	}
	
	public function getDeaths()
	{
		return $this->deaths;
	}
	
	public function increaseDeaths($deaths)
	{
		if($deaths < 0)
			throw new Exception('Trying to increase negative deaths.');
		$this->setDeaths($this->getDeaths()+$deaths);
	}
	
	public function setDeaths($deaths)
	{
		if($this->deaths == $deaths)
			return;
		$this->deaths = $deaths;
		$this->hasChanged=true;
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
	
	public function setExperience($experience)
	{
		if($this->experience == $experience)
			return;
		if($experience<MIN_EXPERIENCE)
			$experience = MIN_EXPERIENCE;
		if($experience>MAX_EXPERIENCE)
			$experience = MAX_EXPERIENCE;
		$this->experience = $experience;
		$this->hasChanged=true;
	}

	function increaseCredits($credits)
	{
		if($credits < 0)
			throw new Exception('Trying to increase negative credits.');
		if($credits == 0)
			return;
		$credits += $this->credits;
		$this->setCredits($credits);
	}
	function decreaseCredits($credits)
	{
		if($credits < 0)
			throw new Exception('Trying to decrease negative credits.');
		if($credits == 0)
			return;
		$credits = $this->credits - $credits;
		$this->setCredits($credits);
	}
	function setCredits($credits)
	{
		if($this->credits == $credits)
			return;
		if (has_privilege('Money Doesn\'t Matter',$this->accountID)) return;
		if($credits < 0)
			throw new Exception('Trying to set negative credits.');
		if($credits > MAX_MONEY)
			throw new Exception('Trying to set more than max credits.');
		$this->credits = $credits;
		$this->hasChanged=true;
//		$this->db->query('UPDATE player SET credits = '.$this->credits.' WHERE '.$this->SQL.' LIMIT 1');
	}
	
	function increaseExperience($experience,$type='Undefined')
	{
		if($experience < 0)
			throw new Exception('Trying to increase negative experience.');
		if($experience == 0)
			return;
		$newExperience = $this->experience + $experience;
		$this->setExperience($newExperience);
		$this->increaseHOF($experience,'Experience',$type);
	}
	function decreaseExperience($experience,$type='Undefined')
	{
		if($experience < 0)
			throw new Exception('Trying to decrease negative experience.');
		if($experience == 0)
			return;
		$newExperience = $this->experience - $experience;
		$this->setExperience($newExperience);
		$this->decreaseHOF($experience,'Experience',$type);
	}
	
	public function isLandedOnPlanet()
	{
		return $this->landedOnPlanet;
	}
	
	public function setLandedOnPlanet($bool)
	{
		if($this->landedOnPlanet == $bool)
			return;
		$this->landedOnPlanet=$bool;
		$this->hasChanged=true;
	}
	
	public function getLevelID()
	{
		$LEVELS_REQUIREMENTS = Globals::getLevelRequirements();
		foreach ($LEVELS_REQUIREMENTS as $level_id => $require)
		{
			if ($this->experience >= $require['Requirement']) continue;
				return $level_id - 1;
		}
		return max(array_keys($LEVELS_REQUIREMENTS));
	}
	
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
	
	public function setPlayerName($name)
	{
		$this->playerName = $name;
		$this->hasChanged = true;
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
	
	public function getColouredRaceName($otherRaceID)
	{
		$races =& Globals::getRaces();
		
		$raceRelations =& Globals::getRaceRelations($this->getGameID(),$this->getRaceID());

		return get_colored_text($raceRelations[$otherRaceID], $races[$otherRaceID]['Race Name']);
	}
	
	public function setRaceID($raceID)
	{
		if($this->raceID == $raceID)
			return;
		$this->raceID=$raceID;
		$this->hasChanged=true;
	}
	
	public function getAllianceID()
	{
		return $this->allianceID;
	}
	
	public function hasAlliance()
	{
		return $this->getAllianceID()!=0;
	}
	
	public function setAllianceID($ID)
	{
		if($this->allianceID == $ID)
			return;
		$this->allianceID=$ID;
		$this->hasChanged=true;
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
	
	protected abstract function getStatsData();
	public function getStats()
	{
		$this->getStatsData();
		return $this->stats;
	}
	
	public function getStat($name)
	{
		$stats = $this->getStats();
		return $stats[$name];
	}
	
	protected abstract function getGadgetsData();
	public function getGadgets()
	{
		$this->getGadgetsData();
		return $this->gadgets;
	}
	
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
	
	abstract public function &getShip();
	
	public function getDiminishingTraderAttack()
	{
		return max(0,min(1, 1 - ($this->getPastMaint() / ($this->getShip()->getSpeed() * 25 + 475))));
	}
	
	public function &shootPlayer(AbstractSmrPlayer &$targetPlayer)
	{
		return $this->getShip()->shootPlayer($targetPlayer);
	}
	
	public function &shootPlayers(array &$targetPlayers)
	{
		return $this->getShip()->shootPlayers($targetPlayers);
	}
	
	public function getMilitaryPayment()
	{
		return $this->militaryPayment;
	}
	
	public function setMilitaryPayment($amount)
	{
		if($this->militaryPayment == $amount)
			return;
		$this->militaryPayment = $amount;
		$this->hasChanged=true;
	}
	
	public function increaseMilitaryPayment($amount)
	{
		if($amount < 0)
			throw new Exception('Trying to increase negative military payment.');
		$this->setMilitaryPayment($this->getMilitaryPayment()+$amount);
	}
	
	public function decreaseMilitaryPayment($amount)
	{
		if($amount < 0)
			throw new Exception('Trying to decrease negative military payment.');
		$this->setMilitaryPayment($this->getMilitaryPayment()-$amount);
	}
	
	abstract protected function getBountiesData();
	
	public function getBounties()
	{
		$this->getBountiesData();
		return $this->bounties;
	}
	
	public function hasBounties()
	{
		return count($this->getBounties())>0;
	}
	
	public function getBounty($bountyID)
	{
		$bounties = $this->getBounties();
		return isset($bounties[$bountyID]) ? $bounties[$bountyID] : false;
	}
	
	public function hasBounty($bountyID)
	{
		return $this->getBounty($bountyID)!==false;
	}
	
	public function getBountyAmount($bountyID)
	{
		$bounties = $this->getBounties();
		return $bounties[$bountyID]['Amount'];
	}
	
	public function createBounty($bountyID,$type)
	{
		if($this->hasBounty($bountyID))
			throw new Exception('Trying to create negative bounty.');
		$bounty = array('Amount' => 0,
						'Type' => $type,
						'Claimer' => 0,
						'Time' => TIME,
						'ID' => $this->getNextBountyID(),
						'New' => true);
		$this->setBounty($bounty);
		$this->hasBountyChanged[$bountyID]=true;
	}
	
	public function getNextBountyID()
	{
		return max(array_keys($this->getBounties()));
	}
	
	public function setBounty(array $bounty)
	{
		$this->bounties[$bounty['ID']] = $bounty;
		$this->hasBountyChanged[$bounty['ID']]=true;
	}
	
	public function setBountyAmounty($bountyID, $amount)
	{
		$bounty = $this->getBounty($bountyID);
		$bounty['Amount'] = $amount;
		$this->setBounty($bounty);
	}
	
	public function increaseBountyAmount($bountyID,$amount)
	{
		if($amount < 0)
			throw new Exception('Trying to increase negative bounty.');
		$this->setBountyAmount($this->getBountyAmount($bountyID)+$amount);
	}
	
	public function decreaseBountyAmount($bountyID,$amount)
	{
		if($amount < 0)
			throw new Exception('Trying to decrease negative bounty.');
		$this->setBountyAmount($this->getBountyAmount($bountyID)+$amount);
	}
	
	abstract protected function getHOFData();
	
	public function getHOF()
	{
		$this->getHOFData();
		return $this->HOF;
	}
	
	public function increaseHOF($amount,$type,$subtype=false) //TODO implement HOF and take away error suppression.
	{
		if($amount < 0)
			throw new Exception('Trying to increase negative HOF: '.$type.'-'.$subtype);
		if($amount == 0)
			return;
		$this->getHOF();
		if($subtype===false)
			$amount+=@$this->HOF[$type];
		else
			$amount+=@$this->HOF[$type][$subtype];
		$this->setHOF($amount,$type,$subtype);
	}
	
	public function decreaseHOF($amount,$type,$subtype=false) //TODO implement HOF and take away error suppression.
	{
		if($amount < 0)
			throw new Exception('Trying to decrease negative HOF: '.$type.'-'.$subtype);
		if($amount == 0)
			return;
		$this->getHOF();
		if($subtype===false)
			$amount=@$this->HOF[$type]-$amount;
		else
			$amount=@$this->HOF[$type][$subtype]-$amount;
		$this->setHOF($amount,$type,$subtype);
	}
	
	public function setHOF($amount,$type,$subtype=false) //TODO implement HOF and take away error suppression.
	{
		if(($subtype!==false && @$this->HOF[$type]==$amount)||@$this->HOF[$type][$subtype]==$amount)
			return;
		if($amount < 0)
			$amount=0;
		$this->getHOF();
		if($subtype===false)
			@$this->HOF[$type]=$amount;
		else
			@$this->HOF[$type][$subtype]=$amount;
//PAGE
//TODO: Add hof stuff.
//		$result = $this->db->query('UPDATE player_hof SET `'.$type.($subtype!==false?'-'.$subtype:'').'`='.$amount.' WHERE ' . $this->SQL);
	}
}
?>