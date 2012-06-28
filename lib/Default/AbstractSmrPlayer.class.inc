<?php
require_once('SmrShip.class.inc');
require_once('SmrForce.class.inc');
require_once('SmrGame.class.inc');
require_once('SmrAlliance.class.inc');
require_once('missions.inc');

abstract class AbstractSmrPlayer {
	protected $db;

	const HOF_CHANGED = 1;
	const HOF_NEW = 2;

	protected $accountID;
	protected $gameID;
	protected $playerName;
	protected $playerID;
	protected $sectorID;
	protected $lastSectorID;
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
	protected $goods;
	protected $goodsExpGained;
	protected $gadgets;
	protected $stats;
	protected $pastMaintBought;
	protected $pastMaintCredit;
	protected $pureRelations;
	protected $relations;
	protected $militaryPayment;
	protected $bounties;
	protected $turns;
	protected $lastCPLAction;
	protected $completedMissions;
	protected $missions;

	protected $visitedSectors;

	protected $HOF;
	protected static $HOFVis;

	protected $hasChanged=false;
	protected $hasHOFChanged=false;
	protected static $hasHOFVisChanged=array();
	protected $hasBountyChanged = array();

	protected function __construct() {
	}

	public function getAccountID() {
		return $this->accountID;
	}

	public function getGameID() {
		return $this->gameID;
	}

	public function &getGame() {
		return SmrGame::getGame($this->gameID);
	}

	public function getNewbieTurns() {
		return $this->newbieTurns;
	}

	public function hasNewbieTurns() {
		return $this->getNewbieTurns()>0;
	}
	public function setNewbieTurns($newbieTurns) {
		if($this->newbieTurns == $newbieTurns)
			return;
		$this->newbieTurns=$newbieTurns;
		$this->hasChanged=true;
	}

	public function getShipTypeID() {
		return $this->shipID;
	}

	public function setShipTypeID($shipID) {
		if($this->shipID == $shipID)
			return;
		$this->shipID=$shipID;
		$this->hasChanged=true;
	}

	public function getLastShieldUpdate() {
		return $this->lastShieldUpdate;
	}

	public function setLastShieldUpdate($time) {
		if($this->lastShieldUpdate == $time)
			return;
		$this->lastShieldUpdate=$time;
		$this->hasChanged=true;
	}

	public function &getSectorPlanet() {
		return SmrPlanet::getPlanet($this->getGameID(),$this->getSectorID());
	}

	public function &getSectorPort() {
		return SmrPort::getPort($this->getGameID(),$this->getSectorID());
	}

	public function getSectorID() {
		return $this->sectorID;
	}

	public function &getSector() {
		return SmrSector::getSector($this->getGameID(),$this->getSectorID());
	}

	public function setSectorID($sectorID) {
		$this->lastSectorID = $this->getSectorID();
		$this->actionTaken('LeaveSector',array('Sector'=>$this->getSector()));
		$this->sectorID = $sectorID;
		$this->actionTaken('EnterSector',array('Sector'=>$this->getSector()));
		$this->hasChanged=true;
	}

	public function getLastSectorID() {
		return $this->lastSectorID;
	}

	public function isDead() {
		return $this->dead;
	}

	public function isNPC() {
		return $this->getAccount()->isNPC();
	}

	public function getSafeAttackRating() {
		return max(0, min(8, $this->getAlignment() / 150 + 4));
	}

	public function hasFederalProtection() {
		$sector =& SmrSector::getSector($this->getGameID(),$this->getSectorID());
		if (!$sector->offersFederalProtection()) {
			return false;
		}

		$ship =& $this->getShip();
		if ($ship->hasIllegalGoods())
			return false;

		if ($ship->getAttackRating() <= $this->getSafeAttackRating()) {
			foreach($sector->getFedRaceIDs() as $fedRaceID) {
				if($this->canBeProtectedByRace($fedRaceID)) {
					return true;
				}
			}
		}

		return false;
	}

	public function canBeProtectedByRace($raceID) {
		if(!isset($this->canFed)) {
			$this->canFed = array();
			$RACES =& Globals::getRaces();
			foreach($RACES as $raceID2 => $raceName) {
				$this->canFed[$raceID2] = $this->getRelation($raceID2) >= ALIGN_FED_PROTECTION;
			}
			$this->db->query('SELECT race_id, allowed FROM player_can_fed
								WHERE account_id = ' . $this->db->escapeNumber($this->getAccountID()) . '
									AND game_id = ' . $this->db->escapeNumber($this->getGameID()) . '
									AND expiry > ' . $this->db->escapeNumber(TIME) . ';');
			while($this->db->nextRecord()) {
				$this->canFed[$this->db->getInt('race_id')] = $this->db->getBoolean('allowed');
			}
		}
		return $this->canFed[$raceID];
	}

	public function canFight() {
		return !$this->hasNewbieTurns() && !$this->isDead() && !$this->hasFederalProtection();
	}

	public function getStartDead() {
		return $this->startDead;
	}

	public function setDead($bool) {
		if($this->dead == $bool)
			return;
		$this->dead=$bool;
		$this->hasChanged=true;
	}

	public function getKills() {
		return $this->kills;
	}

	public function increaseKills($kills) {
		if($kills < 0)
			throw new Exception('Trying to increase negative kills.');
		$this->setKills($this->kills+$kills);
	}

	public function setKills($kills) {
		if($this->kills == $kills)
			return;
		$this->kills=$kills;
		$this->hasChanged=true;
	}

	public function getDeaths() {
		return $this->deaths;
	}

	public function increaseDeaths($deaths) {
		if($deaths < 0)
			throw new Exception('Trying to increase negative deaths.');
		$this->setDeaths($this->getDeaths()+$deaths);
	}

	public function setDeaths($deaths) {
		if($this->deaths == $deaths)
			return;
		$this->deaths = $deaths;
		$this->hasChanged=true;
	}

	public function getAlignment() {
		return $this->alignment;
	}

	public function increaseAlignment($align) {
		if($align < 0)
			throw new Exception('Trying to increase negative align.');
		if($align == 0)
			return;
		$align += $this->alignment;
		$this->setAlignment($align);
	}
	public function decreaseAlignment($align) {
		if($align < 0)
			throw new Exception('Trying to decrease negative align.');
		if($align == 0)
			return;
		$align = $this->alignment - $align;
		$this->setAlignment($align);
	}
	public function setAlignment($align) {
		if($this->alignment == $align)
			return;
		$this->alignment = $align;
		$this->hasChanged=true;
	}

	public function getCredits() {
		return $this->credits;
	}

	public function getExperience() {
		return $this->experience;
	}

	public function getNextLevelPercentAcquired() {
		if($this->getNextLevelExperience() == $this->getThisLevelExperience())
			return 100;
		return max(0,min(100,round(($this->getExperience() - $this->getThisLevelExperience()) / ($this->getNextLevelExperience() - $this->getThisLevelExperience())*100)));
	}

	public function getNextLevelPercentRemaining() {
		return 100 - $this->getNextLevelPercentAcquired();
	}

	public function getNextLevelExperience() {
		$LEVELS_REQUIREMENTS = Globals::getLevelRequirements();
		if(!isset($LEVELS_REQUIREMENTS[$this->getLevelID()+1]))
			return $this->getThisLevelExperience(); //Return current level experience if on last level.
		return $LEVELS_REQUIREMENTS[$this->getLevelID()+1]['Requirement'];
	}

	public function getThisLevelExperience() {
		$LEVELS_REQUIREMENTS = Globals::getLevelRequirements();
		return $LEVELS_REQUIREMENTS[$this->getLevelID()]['Requirement'];
	}

	public function setExperience($experience) {
		if($this->experience == $experience)
			return;
		if($experience<MIN_EXPERIENCE)
			$experience = MIN_EXPERIENCE;
		if($experience>MAX_EXPERIENCE)
			$experience = MAX_EXPERIENCE;
		$this->experience = $experience;
		$this->hasChanged=true;
	}

	public function increaseCredits($credits) {
		if($credits < 0)
			throw new Exception('Trying to increase negative credits.');
		if($credits == 0)
			return;
		$credits += $this->credits;
		$this->setCredits($credits);
	}
	public function decreaseCredits($credits) {
		if($credits < 0)
			throw new Exception('Trying to decrease negative credits.');
		if($credits == 0)
			return;
		$credits = $this->credits - $credits;
		$this->setCredits($credits);
	}
	public function setCredits($credits) {
		if($this->credits == $credits)
			return;
		if (has_privilege('Money Doesn\'t Matter',$this->accountID)) return;
		if($credits < 0)
			throw new Exception('Trying to set negative credits.');
		if($credits > MAX_MONEY)
			throw new Exception('Trying to set more than max credits.');
		$this->credits = $credits;
		$this->hasChanged=true;
	}

	public function increaseExperience($experience) {
		if($experience < 0)
			throw new Exception('Trying to increase negative experience.');
		if($experience == 0)
			return;
		$newExperience = $this->experience + $experience;
		$this->setExperience($newExperience);
		$this->increaseHOF($experience,array('Experience','Total','Gain'), HOF_PUBLIC);
	}
	public function decreaseExperience($experience) {
		if($experience < 0)
			throw new Exception('Trying to decrease negative experience.');
		if($experience == 0)
			return;
		$newExperience = $this->experience - $experience;
		$this->setExperience($newExperience);
		$this->decreaseHOF($experience,array('Experience','Total','Loss'), HOF_PUBLIC);
	}

	public function isLandedOnPlanet() {
		return $this->landedOnPlanet;
	}

	public function setLandedOnPlanet($bool) {
		if($this->landedOnPlanet == $bool)
			return;
		$this->landedOnPlanet=$bool;
		$this->hasChanged=true;
	}

	public function getLevelID() {
		$LEVELS_REQUIREMENTS =& Globals::getLevelRequirements();
		foreach ($LEVELS_REQUIREMENTS as $level_id => $require) {
			if ($this->getExperience() >= $require['Requirement']) continue;
			return $level_id - 1;
		}
		return max(array_keys($LEVELS_REQUIREMENTS));
	}

	public function getLevelName() {
		if($this->isPresident())
			return Globals::getRaceName($this->getRaceID()) . ' President';
		$LEVEL_REQUIREMENTS = Globals::getLevelRequirements();
		if ($this->getAlignment() > 150) $a_name = 'Federation';
		elseif ($this->getAlignment() < -150) $a_name = 'Underground';
		else $a_name = 'Sector';
		$level_name = $LEVEL_REQUIREMENTS[$this->getLevelID()]['Name'];
		$level_name = str_replace('Race',Globals::getRaceName($this->getRaceID()),$level_name);
		$level_name = str_replace('Alignment',$a_name,$level_name);
		return $level_name;
	}

	public function getPlayerID() {
		return $this->playerID;
	}

	public function getPlayerName() {
		return $this->playerName;
	}

	public function setPlayerName($name) {
		$this->playerName = $name;
		$this->hasChanged = true;
	}

	public function getName() {
		return $this->playerName;
	}

	public function getDisplayName($includeAlliance=false) {
		$return = get_colored_text($this->getAlignment(),$this->getName().' ('.$this->getPlayerID().')');
		if($includeAlliance) {
			$return.= ' (' . $this->getAllianceName() . '</a>' . ')';
		}
//NPC: get_colored_name($dead_guy->getPlayerName(), $dead_guy->getAlignment()) . ' <span class='npcColor'>[NPC]</span>';
			return $return;
	}

	public function getBBLink() {
			return '[player='.$this->getPlayerID().']';
	}

	public function getLinkedDisplayName($includeAlliance=true) {
		$return = '<a href="'.$this->getTraderSearchHREF().'">'.$this->getDisplayName().'</a>';
		if($includeAlliance) {
			$return .= ' (' . $this->getAllianceName(true) . ')';
		}
		return $return;
	}

	public function getRaceID() {
		return $this->raceID;
	}

	public function getRaceName() {
		return Globals::getRaceName($this->getRaceID());
	}

	public static function getColouredRaceNameOrDefault($otherRaceID, AbstractSmrPlayer &$player = null, $linked = false) {
		$relations = 0;
		if($player !== null) {
			$relations = $player->getRelation($otherRaceID);
		}
		$raceName = get_colored_text($relations, Globals::getRaceName($otherRaceID));
		if($linked === true) {
			$container = create_container('skeleton.php','council_list.php', array('race_id' => $otherRaceID));
			$raceName = create_link($container, $raceName);
		}
		return $raceName;
	}

	public function getColouredRaceName($otherRaceID, $linked = false) {
		return self::getColouredRaceNameOrDefault($otherRaceID, $this, $linked);
	}

	public function setRaceID($raceID) {
		if($this->raceID == $raceID)
			return;
		$this->raceID=$raceID;
		$this->hasChanged=true;
	}

	public function isAllianceLeader($forceUpdate = false) {
		return $this->getAccountID() == $this->getAlliance($forceUpdate)->getLeaderID();
	}

	public function &getAlliance($forceUpdate = false) {
		return SmrAlliance::getAlliance($this->getAllianceID(), $this->getGameID(), $forceUpdate);
	}

	public function getAllianceID() {
		return $this->allianceID;
	}

	public function hasAlliance() {
		return $this->getAllianceID()!=0;
	}

	public function setAllianceID($ID) {
		if($this->allianceID == $ID)
			return;
		$this->allianceID=$ID;
		if($this->allianceID!=0)
			$this->db->query('INSERT IGNORE INTO player_joined_alliance (account_id,game_id,alliance_id,status) ' .
				'VALUES ('.$this->db->escapeNumber($this->getAccountID()).','.$this->db->escapeNumber($this->getGameID()).','.$this->db->escapeNumber($this->getAllianceID()).','.$this->db->escapeString($this->getAccount()->isVeteran()?'VETERAN':'NEWBIE').')');
		$this->hasChanged=true;
	}

	public function getAllianceBBLink() {
		return $this->hasAlliance()?'[alliance='.$this->getAllianceID().']':$this->getAllianceName();
	}

	public function getAllianceName($linked = false) {
		if($this->hasAlliance()) {
			return $this->getAlliance()->getAllianceName($linked);
		}
		else {
			return 'No Alliance';
		}
	}

	public function getSpanColour() {
		return get_colored_name('NA', $this->alignment, 'Game', 'Color');
	}

	public function isCombatDronesKamikazeOnMines() {
		return $this->combatDronesKamikazeOnMines;
	}

	public function setCombatDronesKamikazeOnMines($bool) {
		if($this->combatDronesKamikazeOnMines == $bool)
			return;
		$this->combatDronesKamikazeOnMines = $bool;
		$this->hasChanged=true;
	}

	public abstract function getPastMaintBought();
	public abstract function getPastMaintCredit();
	public function getPastMaint() {
		$pastMaint = $this->getPastMaintBought()+$this->getPastMaintCredit();
		if ($pastMaint < 0) return 0;
		return $pastMaint;
	}

	protected abstract function getStatsData();
	public function getStats() {
		$this->getStatsData();
		return $this->stats;
	}

	public function getStat($name) {
		$stats = $this->getStats();
		return isset($stats[$name]) ? $stats[$name] : 0;
	}

	protected abstract function getGadgetsData();
	public function getGadgets() {
		$this->getGadgetsData();
		return $this->gadgets;
	}

	public function getGadget($gadgetID) {
		if(!is_numeric($gadgetID)) {
			global $GADGETS;
			$gadgetID = $GADGETS[$gadgetID]['ID'];
		}
		$gadgets = $this->getGadgets();
		if(isset($gadgets[$gadgetID]))
			return $gadgets[$gadgetID];
		return false;
	}

	public function isGadgetEquipped($gadgetID) {
		$gadget = $this->getGadget($gadgetID);
		if($gadget===false)
			return false;
		return $gadget['Equipped'] > 0 && $gadget['Equipped'] < TIME && ($gadget['Expires'] == 0 || $gadget['Expires'] > TIME) && $gadget['Cooldown'] <= TIME;
	}

	protected abstract function getPureRelationsData();

	public function getPureRelations() {
		$this->getPureRelationsData();
		return $this->pureRelations;
	}

	public function getPureRelation($raceID) {
		$rels = $this->getPureRelations();
		return $rels[$raceID];
	}

	public function getRelations() {
		if(!isset($this->relations)) {
			//get relations
			$RACES = Globals::getRaces();
			$raceRelations =& Globals::getRaceRelations($this->getGameID(),$this->getRaceID());
			$pureRels = $this->getPureRelations(); // make sure they're initialised.
//			$stats = $this->getStats(); // make sure they're initialised.
			$this->relations = array();
			foreach ($RACES as $raceID => $raceName) {
				$raceRelations =& Globals::getRaceRelations($this->getGameID(), $raceID);
				$this->relations[$raceID] = round($pureRels[$raceID] + $raceRelations[$this->getRaceID()]);// + $stats['Relations'] / 2);
			}
		}
		return $this->relations;
	}

	public function getRelation($raceID) {
		$rels = $this->getRelations();
		return $rels[$raceID];
	}

	abstract public function &getShip();

	public function getDiminishingPortAttack() {
		return max(0,min(1, 1 - ($this->getPastMaint() / ($this->getShip()->getSpeed() * 25 + 450))));
	}

	public function getDiminishingPlanetAttack() {
		return max(0,min(1, 1 - ($this->getPastMaint() / ($this->getShip()->getSpeed() * 25 + 450))));
	}

	public function getDiminishingForceAttack() {
		return max(0,min(1, 1 - ($this->getPastMaint() / ($this->getShip()->getSpeed() * 25 + 900))));
	}

	public function getDiminishingTraderAttack() {
		return max(0,min(1, 1 - ($this->getPastMaint() / ($this->getShip()->getSpeed() * 25 + 475))));
	}

	public function &shootPlayer(AbstractSmrPlayer &$targetPlayer) {
		return $this->getShip()->shootPlayer($targetPlayer);
	}

	public function &shootForces(SmrForce &$forces) {
		return $this->getShip()->shootForces($forces);
	}

	public function &shootPort(SmrPort &$port) {
		return $this->getShip()->shootPort($port);
	}

	public function &shootPlanet(SmrPlanet &$planet, $delayed) {
		return $this->getShip()->shootPlanet($planet, $delayed);
	}

	public function &shootPlayers(array &$targetPlayers) {
		return $this->getShip()->shootPlayers($targetPlayers);
	}

	public function getMilitaryPayment() {
		return $this->militaryPayment;
	}

	public function hasMilitaryPayment() {
		return $this->getMilitaryPayment()>0;
	}

	public function setMilitaryPayment($amount) {
		if($this->militaryPayment == $amount)
			return;
		$this->militaryPayment = $amount;
		$this->hasChanged=true;
	}

	public function increaseMilitaryPayment($amount) {
		if($amount < 0)
			throw new Exception('Trying to increase negative military payment.');
		$this->setMilitaryPayment($this->getMilitaryPayment()+$amount);
	}

	public function decreaseMilitaryPayment($amount) {
		if($amount < 0)
			throw new Exception('Trying to decrease negative military payment.');
		$this->setMilitaryPayment($this->getMilitaryPayment()-$amount);
	}

	abstract protected function getBountiesData();

	public function getBounties() {
		$this->getBountiesData();
		return $this->bounties;
	}

	public function hasBounties() {
		return count($this->getBounties())>0;
	}

	public function getBounty($bountyID) {
		$bounties = $this->getBounties();
		return isset($bounties[$bountyID]) ? $bounties[$bountyID] : false;
	}

	public function hasBounty($bountyID) {
		return $this->getBounty($bountyID)!==false;
	}

	public function getBountyAmount($bountyID) {
		$bounty = $this->getBounty($bountyID);
		return $bounty['Amount'];
	}

	protected function createBounty($type) {
		$bounty = array('Amount' => 0,
						'SmrCredits' => 0,
						'Type' => $type,
						'Claimer' => 0,
						'Time' => TIME,
						'ID' => $this->getNextBountyID(),
						'New' => true);
		$this->setBounty($bounty);
	}

	public function getNextBountyID() {
		$keys = array_keys($this->getBounties());
		if(count($keys)>0)
			return max($keys)+1;
		else
			return 0;
	}

	public function setBounty(array $bounty) {
		$this->bounties[$bounty['ID']] = $bounty;
		$this->hasBountyChanged[$bounty['ID']]=true;
	}

	public function setBountyAmount($bountyID, $amount) {
		$bounty = $this->getBounty($bountyID);
		$bounty['Amount'] = $amount;
		$this->setBounty($bounty);
	}

	public function increaseBountyAmount($bountyID,$amount) {
		if($amount < 0)
			throw new Exception('Trying to increase negative bounty.');
		$this->setBountyAmount($this->getBountyAmount($bountyID)+$amount);
	}

	public function decreaseBountyAmount($bountyID,$amount) {
		if($amount < 0)
			throw new Exception('Trying to decrease negative bounty.');
		$this->setBountyAmount($this->getBountyAmount($bountyID)+$amount);
	}

	public function getCurrentBounty($type) {
		$bounties = $this->getBounties();
		foreach($bounties as $bounty) {
			if($bounty['Claimer'] == 0 && $bounty['Type']==$type)
				return $bounty;
		}
		return $this->createBounty($type);
	}

	public function hasCurrentBounty($type) {
		$bounties = $this->getBounties();
		foreach($bounties as $bounty) {
			if($bounty['Claimer'] == 0 && $bounty['Type']==$type)
				return true;
		}
		return false;
	}

	public function getCurrentBountyAmount($type) {
		$bounty = $this->getCurrentBounty($type);
		return $bounty['Amount'];
	}

	public function setCurrentBountyAmount($type, $amount) {
		$bounty = $this->getCurrentBounty($type);
		if($bounty['Amount'] == $amount)
			return;
		$bounty['Amount'] = $amount;
		$this->setBounty($bounty);
	}

	public function increaseCurrentBountyAmount($type,$amount) {
		if($amount < 0)
			throw new Exception('Trying to increase negative current bounty.');
		$this->setCurrentBountyAmount($type,$this->getCurrentBountyAmount($type)+$amount);
	}

	public function decreaseCurrentBountyAmount($type,$amount) {
		if($amount < 0)
			throw new Exception('Trying to decrease negative current bounty.');
		$this->setCurrentBountyAmount($type,$this->getCurrentBountyAmount($type)-$amount);
	}

	public function getCurrentBountySmrCredits($type) {
		$bounty = $this->getCurrentBounty($type);
		return $bounty['SmrCredits'];
	}

	public function setCurrentBountySmrCredits($type, $credits) {
		$bounty = $this->getCurrentBounty($type);
		if($bounty['SmrCredits'] == $credits)
			return;
		$bounty['SmrCredits'] = $credits;
		$this->setBounty($bounty);
	}

	public function increaseCurrentBountySmrCredits($type,$credits) {
		if($credits<0)
			throw new Exception('Trying to increase negative current bounty.');
		$this->setCurrentBountySmrCredits($type,$this->getCurrentBountySmrCredits($type)+$credits);
	}

	public function decreaseCurrentBountySmrCredits($type,$credits) {
		if($credits<0)
			throw new Exception('Trying to decrease negative current bounty.');
		$this->setCurrentBountySmrCredits($type,$this->getCurrentBountySmrCredits($type)-$credits);
	}

	public function setBountiesClaimable(AbstractSmrPlayer $claimer) {
		$bounties = $this->getBounties();
		if(is_array($bounties)) {
			foreach($bounties as $bounty) {
				if($bounty['Claimer'] == 0) {
					$bounty['Claimer'] = $claimer->getAccountID();
					$this->setBounty($bounty);
				}
			}
		}
	}


	abstract protected function getHOFData();

	public function getHOF(array $typeList = null) {
		$this->getHOFData();
		if($typeList==null)
			return $this->HOF;
		$hof=$this->HOF;
		foreach($typeList as $type) {
			if(!isset($hof[$type]))
				return 0;
			$hof = $hof[$type];
		}
		return $hof;
	}

	public function increaseHOF($amount,array $typeList, $visibility) {
		if($amount < 0)
			throw new Exception('Trying to increase negative HOF: '.implode(':',$typeList));
		if($amount == 0)
			return;
		$this->setHOF($this->getHOF($typeList)+$amount,$typeList, $visibility);
	}

	public function decreaseHOF($amount,array $typeList, $visibility) {
		if($amount < 0)
			throw new Exception('Trying to decrease negative HOF: '.implode(':',$typeList));
		if($amount == 0)
			return;
		$this->setHOF($this->getHOF($typeList)-$amount,$typeList, $visibility);
	}

	public function setHOF($amount,array $typeList, $visibility) {
		if(is_array($this->getHOF($typeList)))
			throw new Exception('Trying to overwrite a HOF type: '.implode(':',$typeList));
		if($this->getHOF($typeList)==$amount)
			return;
		if($amount < 0)
			$amount=0;
		$this->getHOF();

		$hofType = implode(':',$typeList);
		if(!isset(self::$HOFVis[$hofType])) {
			self::$hasHOFVisChanged[$hofType] = self::HOF_NEW;
		}
		else if(self::$HOFVis[$hofType] != $visibility) {
			self::$hasHOFVisChanged[$hofType] = self::HOF_CHANGED;
		}
		self::$HOFVis[$hofType] = $visibility;

		$hof =& $this->HOF;
		$hofChanged =& $this->hasHOFChanged;
		$new = false;
		foreach($typeList as $type) {
			if(!isset($hofChanged[$type]))
				$hofChanged[$type] = array();
			if(!isset($hof[$type])) {
				$hof[$type] = array();
				$new = true;
			}
			$hof =& $hof[$type];
			$hofChanged =& $hofChanged[$type];
		}
		if($hofChanged==null) {
			$hofChanged = self::HOF_CHANGED;
			if($new)
				$hofChanged = self::HOF_NEW;
		}
		$hof = $amount;
	}

	abstract public function killPlayer($sectorID);
	abstract public function &killPlayerByPlayer(AbstractSmrPlayer &$killer);
	abstract public function &killPlayerByForces(SmrForce &$forces);
	abstract public function &killPlayerByPort(SmrPort &$port);
	abstract public function &killPlayerByPlanet(SmrPlanet &$planet);
	abstract public function getHome();


	public function getTurns() {
		return $this->turns;
	}

	public function hasTurns() {
		return $this->turns>0;
	}

	public function getMaxTurns() {
		return Globals::getGameMaxTurns($this->getGameID());
//		round(DEFAULT_MAX_TURNS * Globals::getGameSpeed($this->getGameID()));
	}

	public function setTurns($turns,$newNoob = false,$updateLastActive = false) {
		if($this->turns == $turns && ($this->newbieTurns == $newNoob || $newNoob==false) && !$updateLastActive)
			return;

		$this->turns = $turns;

		if(!has_beta_privilege('No SC Change')) {
			if ($this->turns < 0)
				$this->turns = 0;
			if ($this->turns > $this->getMaxTurns())
				$this->turns = $this->getMaxTurns();
		}

		if($newNoob !== false)
			$this->newbieTurns = $newNoob;
		if ($this->newbieTurns < 0)
			$this->newbieTurns = 0;

		if (has_privilege('Unlimited Maintenance'))
			$this->turns = $this->getMaxTurns();

		$this->hasChanged=true;
		if($updateLastActive === true) {
			$this->setLastActive(TIME);
			$this->updateLastCPLAction();
		}
	}

	public function takeTurns($take, $noob = 0,$updateLastActive = true) { // taking maint counts as activity by default
		if($take < 0 || $noob < 0)
			throw new Exception('Trying to take negative turns.');
		$take = ceil($take);
		$new_maint = $this->getTurns() - $take;
		$newbiesTaken = min($this->getNewbieTurns(),$noob);
		$new_noob = $this->getNewbieTurns() - $noob;

		if(!has_beta_privilege('No SC Loss'))
			$this->setTurns($new_maint,$new_noob,$updateLastActive);
		$this->increaseHOF($take,array('Movement','Turns Used','Since Last Death'), HOF_ALLIANCE);
		$this->increaseHOF($take,array('Movement','Turns Used','Total'), HOF_ALLIANCE);
		$this->increaseHOF($newbiesTaken,array('Movement','Turns Used','Newbie'), HOF_ALLIANCE);
	}

	public function giveTurns($give, $noob = 0,$updateLastActive = false) {
		if($give < 0 || $noob < 0)
			throw new Exception('Trying to give negative turns.');
		$give = floor($give);

		if(!has_beta_privilege('No SC Gain'))
			$this->setTurns($this->getTurns() + $give,$this->getNewbieTurns() + $noob,$updateLastActive);
	}

	public function getLastActive() {
		return $this->lastActive;
	}

	public function setLastActive($lastActive) {
		if($this->lastActive == $lastActive)
			return;
		$this->lastActive=$lastActive;
		$this->hasChanged=true;
	}

	public function getLastCPLAction() {
		return $this->lastCPLAction;
	}

	public function setLastCPLAction($time) {
		if($this->lastCPLAction == $time)
			return;
		$this->lastCPLAction=$time;
		$this->hasChanged=true;
	}

	public function updateLastCPLAction() {
		$this->setLastCPLAction(TIME);
	}


	function getCompletedMissions() {
		if(!isset($this->completedMissions)) {
			//Get Player missions
			$this->db->query('SELECT mission_id FROM player_completed_mission WHERE ' . $this->SQL);
			$this->completedMissions = array();
			while ($this->db->nextRecord())
				$this->completedMissions[$this->db->getField('mission_id')] = $this->db->getField('mission_id');
		}
		return $this->completedMissions;
	}

	function getMissions() {
		if(!isset($this->missions)) {
			//Get Player missions
			global $MISSIONS;
			$this->db->query('SELECT * FROM player_has_mission WHERE ' . $this->SQL);
			$this->missions = array();
			while ($this->db->nextRecord()) {
				$this->missions[$this->db->getField('mission_id')] = array(
								'On Step' => $this->db->getField('on_step'),
								'Total' => $this->db->getField('total_steps'),
								'Progress' => $this->db->getField('progress'),
								'Next Step' => $this->db->getField('next_step'),
								'Expires' => $this->db->getField('step_fails'),
								'Mission ID' => $this->db->getField('mission_id'),
								'Sector' => $this->db->getField('mission_sector'),
								'Starting Sector' => $this->db->getField('starting_sector')
								);
				$this->rebuildMission($this->db->getField('mission_id'));
			}
		}
		return $this->missions;
	}

	function getMission($missionID) {
		$missions = $this->getMissions();
		if(isset($missions[$missionID]))
			return $missions[$missionID];
		return false;
	}

	function hasMission($missionID) {
		return $this->getMission($missionID) !== false;
	}

	function updateMission($missionID,$keyOrUpdateArray,$newInfo=false) {
		$this->getMissions();
		if(isset($this->missions[$missionID])) {
			if(is_array($keyOrUpdateArray)) {
				$set = '';
				foreach($keyOrUpdateArray as $bit => $newInfo) {
					$field = $this->missionKeyToField($bit);
					if($field!==false) {
						$this->missions[$missionID][$bit]=$newInfo;
						$set .= '`'.$field.'` = '.$this->db->escapeString($newInfo).',';
					}
				}
				if($set!=='') {
					$set=substr($set,0,-1);
					$this->db->query('UPDATE player_has_mission SET '.$set.' WHERE '.$this->SQL.' AND mission_id = '.$this->db->escapeNumber($missionID).' LIMIT 1');
					$this->rebuildMission($missionID);
					return true;
				}
			}
			else {
				$field = $this->missionKeyToField($keyOrUpdateArray);
				if($field!==false) {
					$this->missions[$missionID][$keyOrUpdateArray]=$newInfo;
					$this->db->query('UPDATE player_has_mission SET `'.$field.'` = '.$this->db->escapeString($newInfo).' WHERE '.$this->SQL.' AND mission_id = '.$this->db->escapeNumber($missionID).' LIMIT 1');
					$this->rebuildMission($missionID);
					return true;
				}
			}
		}
		return false;
	}

	function addMission($missionID,$randomSector=0) {
		global $MISSIONS;
		$this->getMissions();

		if(isset($this->missions[$missionID]))
			return;

		$mission = $MISSIONS[$missionID];
		$playerMission = array(
						'On Step' => 1,
						'Total' => sizeof($mission['Steps']),
						'Progress' => 0,
						'Next Step' => 'TRUE',
						'Expires' => (TIME + 86400),
						'Mission ID' => $missionID,
						'Sector' => $randomSector,
						'Starting Sector' => $this->getSectorID()
						);

		$this->db->query('REPLACE INTO player_has_mission (game_id,account_id,mission_id,on_step,progress,next_step,total_steps,starting_sector,mission_sector,step_fails) ' .
				'VALUES ('.$this->db->escapeNumber($this->gameID).','.$this->db->escapeNumber($this->accountID).','.$this->db->escapeNumber($missionID).','.$this->db->escapeNumber($playerMission['On Step']).','.$this->db->escapeNumber($playerMission['Progress']).','.$this->db->escapeString($playerMission['Next Step']).','.$this->db->escapeNumber($playerMission['Total']).','.$this->db->escapeNumber($playerMission['Starting Sector']).','.$this->db->escapeNumber($playerMission['Sector']).','.$this->db->escapeNumber($playerMission['Expires']).')');
		$this->missions[$missionID] = $playerMission;
		$this->rebuildMission($missionID);
	}

	function rebuildMission($missionID) {
		global $MISSIONS;
		$RACES = Globals::getRaces();
		$missionInfo = $this->missions[$missionID];
		$currentStep = $MISSIONS[$missionID]['Steps'][$missionInfo['On Step']];
		$detail = str_replace(array('<Race>','<Sector>','<Starting Sector>','<trader>'),array($RACES[$this->getRaceID()],$missionInfo['Sector'],$missionInfo['Starting Sector'],$this->playerName),$currentStep['Detail']);
		$text = str_replace(array('<Race>','<Sector>','<Starting Sector>','<trader>'),array($RACES[$this->getRaceID()],$missionInfo['Sector'],$missionInfo['Starting Sector'],$this->playerName),$currentStep['Text']);
		$task = str_replace(array('<Race>','<Sector>','<Starting Sector>','<trader>'),array($RACES[$this->getRaceID()],$missionInfo['Sector'],$missionInfo['Starting Sector'],$this->playerName),$currentStep['Task']);
		if (isset($currentStep['Level']))
			$step_level = str_replace('<Player Level>',$this->levelID,$currentStep['Level']);
		else $step_level = 0;
		$this->missions[$missionID]['Name'] = $MISSIONS[$missionID]['Name'];
		$this->missions[$missionID]['Location'] = $MISSIONS[$missionID]['Location'];
		$this->missions[$missionID]['Task']['Step'] = $currentStep['Step'];
		$this->missions[$missionID]['Task']['Level'] = $step_level;
		$this->missions[$missionID]['Task']['Detail'] = $detail;
		$this->missions[$missionID]['Task']['Text'] = $text;
		$this->missions[$missionID]['Task']['Task'] = $task;
	}

	function deleteMission($missionID) {
		$this->getMissions();
		if(isset($this->missions[$missionID])) {
			unset($this->missions[$missionID]);
			$this->db->query('DELETE FROM player_has_mission WHERE '.$this->SQL.' AND mission_id = '.$this->db->escapeNumber($missionID).' LIMIT 1');
			return true;
		}
		return false;
	}

	protected function missionKeyToField($bit) {
		$field = false;
		switch($bit) {
			case 'On Step':
				$field='on_step';
			break;
			case 'Total':
				$field='total_steps';
			break;
			case 'Progress':
				$field='progress';
			break;
			case 'Next Step':
				$field='next_step';
			break;
			case 'Expires':
				$field='step_fails';
			break;
			case 'Mission ID':
				$field='mission_id';
			break;
		}
		return $field;
	}

	public function getAvailableMissions() {
		global $MISSIONS;
		$availableMissions = array();
		foreach($MISSIONS as $mission) {
			if($this->hasMission($mission['ID']))
				continue;
			if($this->getSector()->hasX($mission['Location']))
				$availableMissions[] = $mission;
		}
		return $availableMissions;
	}

	public function actionTaken($actionID, array $values) {
		global $MISSION_ACTIONS;
		if(!in_array($actionID,$MISSION_ACTIONS))
			throw new Exception('Unknown action: ' . $actionID);
// TODO: Reenable this once tested.		if($this->getAccount()->isLoggingEnabled())
			switch($actionID) {
				case 'WalkSector':
					$this->getAccount()->log(LOG_TYPE_MOVEMENT, 'Walks to sector: ' . $values['Sector']->getSectorID(), $this->getSectorID());
				break;
				case 'JoinAlliance':
					$this->getAccount()->log(LOG_TYPE_ALLIANCE, 'joined alliance: '.$values['Alliance']->getAllianceName(), $this->getSectorID());
				break;
				case 'LeaveAlliance':
					$this->getAccount()->log(LOG_TYPE_ALLIANCE, 'left alliance: '.$values['Alliance']->getAllianceName(), $this->getSectorID());
				break;
				case 'DisbandAlliance':
					$this->getAccount()->log(LOG_TYPE_ALLIANCE, 'disbanded alliance '.$values['Alliance']->getAllianceName(), $this->getSectorID());
				break;
				case 'KickPlayer':
					$this->getAccount()->log(LOG_TYPE_ALLIANCE, 'kicked '.$values['Player']->getAccount()->getLogin().' ('.$values['Player']->getPlayerName().') from alliance '.$values['Alliance']->getAllianceName(), 0);
				break;
				case 'PlayerKicked':
					$this->getAccount()->log(LOG_TYPE_ALLIANCE, 'was kicked from alliance '.$values['Alliance']->getAllianceName().' by '.$values['Player']->getAccount()->getLogin().' ('.$values['Player']->getPlayerName().')', 0);
				break;

			}

	}

	public function canSeeAny(array &$otherPlayerArray) {
		foreach($otherPlayerArray as &$otherPlayer) {
			if($this->canSee($otherPlayer)) {
				return true;
			}
		}
		return false;
	}

	public function canSee(AbstractSmrPlayer &$otherPlayer) {
		if(!$otherPlayer->getShip()->isCloaked())
			return true;
		if($this->sameAlliance($otherPlayer))
			return true;
		if($this->getExperience()>=$otherPlayer->getExperience())
			return true;
		return false;
	}

	public function equals(AbstractSmrPlayer &$otherPlayer = null) {
		return $otherPlayer!==null && $this->getAccountID()==$otherPlayer->getAccountID()&&$this->getGameID()==$otherPlayer->getGameID();
	}

	public function sameAlliance(AbstractSmrPlayer &$otherPlayer = null) {
		return $otherPlayer != null && ($this->equals($otherPlayer) || ($this->hasAlliance()&&$this->getAllianceID()==$otherPlayer->getAllianceID()));
	}

	public function sharedForceAlliance(AbstractSmrPlayer &$otherPlayer = null) {
		return $this->sameAlliance($otherPlayer);
	}

	public function forceNAPAlliance(AbstractSmrPlayer &$otherPlayer = null) {
		return $this->sameAlliance($otherPlayer);
	}

	public function planetNAPAlliance(AbstractSmrPlayer &$otherPlayer = null) {
		return $this->sameAlliance($otherPlayer);
	}

	public function traderNAPAlliance(AbstractSmrPlayer &$otherPlayer = null) {
		return $this->sameAlliance($otherPlayer);
	}

	public function traderMAPAlliance(AbstractSmrPlayer &$otherPlayer = null) {
		return $this->traderAttackTraderAlliance($otherPlayer) && $this->traderDefendTraderAlliance($otherPlayer);
	}

	public function traderAttackTraderAlliance(AbstractSmrPlayer &$otherPlayer = null) {
		return $this->sameAlliance($otherPlayer);
	}

	public function traderDefendTraderAlliance(AbstractSmrPlayer &$otherPlayer = null) {
		return $this->sameAlliance($otherPlayer);
	}

	public function traderAttackForceAlliance(AbstractSmrPlayer &$otherPlayer = null) {
		return $this->sameAlliance($otherPlayer);
	}

	public function traderAttackPortAlliance(AbstractSmrPlayer &$otherPlayer = null) {
		return $this->sameAlliance($otherPlayer);
	}

	public function traderAttackPlanetAlliance(AbstractSmrPlayer &$otherPlayer = null) {
		return $this->sameAlliance($otherPlayer);
	}

	public function meetsAlignmentRestriction($restriction) {
		if($restriction<0)
			return $this->getAlignment()<=$restriction;
		if($restriction>0)
			return $this->getAlignment()>=$restriction;
		return true;
	}

	/**
	 * Will retrieve all visited sectors, use only when you are likely to check a large number of these
	 */
	public function hasVisitedSector($sectorID) {
		if(!isset($this->visitedSectors)) {
			$this->visitedSectors = array();
			$this->db->query('SELECT sector_id FROM player_visited_sector WHERE account_id=' . $this->db->escapeNumber($this->getAccountID()) . ' AND game_id=' . $this->db->escapeNumber($this->getGameID()));
			while($this->db->nextRecord())
				$this->visitedSectors[$this->db->getField('sector_id')] = false;
		}
		return !isset($this->visitedSectors[$sectorID]);
	}

	public function getExamineTraderHREF() {
		$container = create_container('skeleton.php','trader_examine.php');
		$container['target'] = $this->getAccountID();
		return SmrSession::getNewHREF($container);
	}

	public function getAttackTraderHREF() {
		return Globals::getAttackTraderHREF($this->getAccountID());
	}

	public function getPlanetKickHREF() {
		$container = create_container('planet_kick_processing.php','trader_attack_processing.php');
		$container['account_id'] = $this->getAccountID();
		return SmrSession::getNewHREF($container);
	}

	public function getTraderSearchHREF() {
		$container = create_container('skeleton.php','trader_search_result.php');
		$container['player_id'] = $this->getPlayerID();
		return SmrSession::getNewHREF($container);
	}

	public function getAllianceRosterHREF() {
		return Globals::getAllianceRosterHREF($this->getAllianceID());
	}

	public function getToggleWeaponHidingHREF() {
		$container = create_container('skeleton.php','toggle_processing.php');
		$container['toggle'] = 'WeaponHiding';
		return SmrSession::getNewHREF($container);
	}
}
?>