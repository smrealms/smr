<?php
require_once('missions.inc');

abstract class AbstractSmrPlayer {
	protected $db;

	const HOF_CHANGED = 1;
	const HOF_NEW = 2;

	protected $accountID;
	protected $gameID;
	protected $playerName; // This is escaped with htmlentities in the db
	protected $playerID;
	protected $sectorID;
	protected $lastSectorID;
	protected $newbieTurns;
	protected $dead;
	protected $npc;
	protected $newbieStatus;
	protected $landedOnPlanet;
	protected $lastActive;
	protected $raceID;
	protected $credits;
	protected $alignment;
	protected $experience;
	protected $level;
	protected $allianceID;
	protected $shipID;
	protected $kills;
	protected $deaths;
	protected $assists;
	protected $gadgets;
	protected $stats;
	protected $pureRelations;
	protected $relations;
	protected $militaryPayment;
	protected $bounties;
	protected $turns;
	protected $lastCPLAction;
	protected $missions;

	protected $visitedSectors;
	protected $allianceRoles = array(
		0 => 0
	);

	protected $draftLeader;
	protected $gpWriter;
	protected $HOF;
	protected static $HOFVis;

	protected $hasChanged = false;
	protected $hasHOFChanged = false;
	protected static $hasHOFVisChanged = array();
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
		return $this->getNewbieTurns() > 0;
	}
	public function setNewbieTurns($newbieTurns) {
		if ($this->newbieTurns == $newbieTurns)
			return;
		$this->newbieTurns = $newbieTurns;
		$this->hasChanged = true;
	}

	public function getShipTypeID() {
		return $this->shipID;
	}

	public function setShipTypeID($shipID) {
		if ($this->shipID == $shipID)
			return;
		$this->shipID = $shipID;
		$this->hasChanged = true;
	}

	/**
	 * Get planet owned by this player.
	 * Returns false if this player does not own a planet.
	 */
	public function getPlanet() {
		$this->db->query('SELECT * FROM planet WHERE game_id=' . $this->db->escapeNumber($this->getGameID()) . ' AND owner_id=' . $this->db->escapeNumber($this->getAccountID()));
		if ($this->db->nextRecord()) {
			return SmrPlanet::getPlanet($this->getGameID(), $this->db->getInt('sector_id'), false, $this->db);
		} else {
			return false;
		}
	}

	public function &getSectorPlanet() {
		return SmrPlanet::getPlanet($this->getGameID(), $this->getSectorID());
	}

	public function &getSectorPort() {
		return SmrPort::getPort($this->getGameID(), $this->getSectorID());
	}

	public function getSectorID() {
		return $this->sectorID;
	}

	public function &getSector() {
		return SmrSector::getSector($this->getGameID(), $this->getSectorID());
	}

	public function setSectorID($sectorID) {
		$this->lastSectorID = $this->getSectorID();
		$this->actionTaken('LeaveSector', array('Sector'=>$this->getSector()));
		$this->sectorID = $sectorID;
		$this->actionTaken('EnterSector', array('Sector'=>$this->getSector()));
		$this->hasChanged = true;
	}

	public function getLastSectorID() {
		return $this->lastSectorID;
	}

	public function isDead() {
		return $this->dead;
	}

	public function isNPC() {
		return $this->npc;
	}

	/**
	 * Does the player have Newbie status?
	 */
	public function hasNewbieStatus() {
		return $this->newbieStatus;
	}

	/**
	 * Update the player's newbie status if it has changed.
	 * This function queries the account, so use sparingly.
	 */
	public function updateNewbieStatus() {
		$accountNewbieStatus = !$this->getAccount()->isVeteran();
		if ($this->newbieStatus != $accountNewbieStatus) {
			$this->newbieStatus = $accountNewbieStatus;
			$this->hasChanged = true;
		}
	}

	public function isDraftLeader() {
		if (!isset($this->draftLeader)) {
			$this->draftLeader = false;
			$this->db->query('SELECT 1 FROM draft_leaders WHERE ' . $this->SQL . ' LIMIT 1');
			if ($this->db->nextRecord()) {
				$this->draftLeader = true;
			}
		}
		return $this->draftLeader;
	}

	public function getGPWriter() {
		if (!isset($this->gpWriter)) {
			$this->gpWriter = false;
			$this->db->query('SELECT position FROM galactic_post_writer WHERE ' . $this->SQL);
			if ($this->db->nextRecord()) {
				$this->gpWriter = $this->db->getField('position');
			}
		}
		return $this->gpWriter;
	}

	public function isGPEditor() {
		return $this->getGPWriter() == 'editor';
	}

	public function getSafeAttackRating() {
		return max(0, min(8, $this->getAlignment() / 150 + 4));
	}

	public function hasFederalProtection() {
		$sector = SmrSector::getSector($this->getGameID(), $this->getSectorID());
		if (!$sector->offersFederalProtection()) {
			return false;
		}

		$ship = $this->getShip();
		if ($ship->hasIllegalGoods())
			return false;

		if ($ship->getAttackRating() <= $this->getSafeAttackRating()) {
			foreach ($sector->getFedRaceIDs() as $fedRaceID) {
				if ($this->canBeProtectedByRace($fedRaceID)) {
					return true;
				}
			}
		}

		return false;
	}

	public function canBeProtectedByRace($raceID) {
		if (!isset($this->canFed)) {
			$this->canFed = array();
			$RACES = Globals::getRaces();
			foreach ($RACES as $raceID2 => $raceName) {
				$this->canFed[$raceID2] = $this->getRelation($raceID2) >= ALIGN_FED_PROTECTION;
			}
			$this->db->query('SELECT race_id, allowed FROM player_can_fed
								WHERE ' . $this->SQL . ' AND expiry > ' . $this->db->escapeNumber(TIME));
			while ($this->db->nextRecord()) {
				$this->canFed[$this->db->getInt('race_id')] = $this->db->getBoolean('allowed');
			}
		}
		return $this->canFed[$raceID];
	}

	/**
	 * Returns a boolean identifying if the player can currently
	 * participate in battles.
	 */
	public function canFight() {
		return !($this->hasNewbieTurns() ||
		         $this->isDead() ||
		         $this->isLandedOnPlanet() ||
		         $this->hasFederalProtection());
	}

	public function setDead($bool) {
		if ($this->dead == $bool)
			return;
		$this->dead = $bool;
		$this->hasChanged = true;
	}

	public function getKills() {
		return $this->kills;
	}

	public function increaseKills($kills) {
		if ($kills < 0)
			throw new Exception('Trying to increase negative kills.');
		$this->setKills($this->kills + $kills);
	}

	public function setKills($kills) {
		if ($this->kills == $kills)
			return;
		$this->kills = $kills;
		$this->hasChanged = true;
	}

	public function getDeaths() {
		return $this->deaths;
	}

	public function increaseDeaths($deaths) {
		if ($deaths < 0)
			throw new Exception('Trying to increase negative deaths.');
		$this->setDeaths($this->getDeaths() + $deaths);
	}

	public function setDeaths($deaths) {
		if ($this->deaths == $deaths)
			return;
		$this->deaths = $deaths;
		$this->hasChanged = true;
	}

	public function getAssists() {
		return $this->assists;
	}

	public function increaseAssists($assists) {
		if ($assists < 1) {
			throw new Exception('Must increase by a positive number.');
		}
		$this->assists += $assists;
		$this->hasChanged = true;
	}

	public function getAlignment() {
		return $this->alignment;
	}

	public function increaseAlignment($align) {
		if ($align < 0)
			throw new Exception('Trying to increase negative align.');
		if ($align == 0)
			return;
		$align += $this->alignment;
		$this->setAlignment($align);
	}
	public function decreaseAlignment($align) {
		if ($align < 0)
			throw new Exception('Trying to decrease negative align.');
		if ($align == 0)
			return;
		$align = $this->alignment - $align;
		$this->setAlignment($align);
	}
	public function setAlignment($align) {
		if ($this->alignment == $align)
			return;
		$this->alignment = $align;
		$this->hasChanged = true;
	}

	public function getCredits() {
		return $this->credits;
	}

	public function getExperience() {
		return $this->experience;
	}

	public function getNextLevelPercentAcquired() {
		if ($this->getNextLevelExperience() == $this->getThisLevelExperience())
			return 100;
		return max(0, min(100, round(($this->getExperience() - $this->getThisLevelExperience()) / ($this->getNextLevelExperience() - $this->getThisLevelExperience()) * 100)));
	}

	public function getNextLevelPercentRemaining() {
		return 100 - $this->getNextLevelPercentAcquired();
	}

	public function getNextLevelExperience() {
		$LEVELS_REQUIREMENTS = Globals::getLevelRequirements();
		if (!isset($LEVELS_REQUIREMENTS[$this->getLevelID() + 1]))
			return $this->getThisLevelExperience(); //Return current level experience if on last level.
		return $LEVELS_REQUIREMENTS[$this->getLevelID() + 1]['Requirement'];
	}

	public function getThisLevelExperience() {
		$LEVELS_REQUIREMENTS = Globals::getLevelRequirements();
		return $LEVELS_REQUIREMENTS[$this->getLevelID()]['Requirement'];
	}

	public function setExperience($experience) {
		if ($this->experience == $experience)
			return;
		if ($experience < MIN_EXPERIENCE)
			$experience = MIN_EXPERIENCE;
		if ($experience > MAX_EXPERIENCE)
			$experience = MAX_EXPERIENCE;
		$this->experience = $experience;
		$this->hasChanged = true;

		// Since exp has changed, invalidate the player level so that it can
		// be recomputed next time it is queried (in case it has changed).
		$this->level = null;
	}

	public function increaseCredits($credits) {
		if ($credits < 0)
			throw new Exception('Trying to increase negative credits.');
		if ($credits == 0)
			return;
		$credits += $this->credits;
		$this->setCredits($credits);
	}
	public function decreaseCredits($credits) {
		if ($credits < 0)
			throw new Exception('Trying to decrease negative credits.');
		if ($credits == 0)
			return;
		$credits = $this->credits - $credits;
		$this->setCredits($credits);
	}
	public function setCredits($credits) {
		if ($this->credits == $credits)
			return;
		if ($credits < 0)
			throw new Exception('Trying to set negative credits.');
		if ($credits > MAX_MONEY)
			$credits = MAX_MONEY;
		$this->credits = $credits;
		$this->hasChanged = true;
	}

	public function increaseExperience($experience) {
		if ($experience < 0)
			throw new Exception('Trying to increase negative experience.');
		if ($experience == 0)
			return;
		$newExperience = $this->experience + $experience;
		$this->setExperience($newExperience);
		$this->increaseHOF($experience, array('Experience', 'Total', 'Gain'), HOF_PUBLIC);
	}
	public function decreaseExperience($experience) {
		if ($experience < 0)
			throw new Exception('Trying to decrease negative experience.');
		if ($experience == 0)
			return;
		$newExperience = $this->experience - $experience;
		$this->setExperience($newExperience);
		$this->decreaseHOF($experience, array('Experience', 'Total', 'Loss'), HOF_PUBLIC);
	}

	public function isLandedOnPlanet() {
		return $this->landedOnPlanet;
	}

	public function setLandedOnPlanet($bool) {
		if ($this->landedOnPlanet == $bool)
			return;
		$this->landedOnPlanet = $bool;
		$this->hasChanged = true;
	}

	/**
	 * Returns the numerical level of the player (e.g. 1-50).
	 */
	public function getLevelID() {
		// The level is cached for performance reasons unless `setExperience`
		// is called and the player's experience changes.
		if ($this->level === null) {
			$LEVELS_REQUIREMENTS = Globals::getLevelRequirements();
			foreach ($LEVELS_REQUIREMENTS as $level_id => $require) {
				if ($this->getExperience() >= $require['Requirement']) continue;
				$this->level = $level_id - 1;
				return $this->level;
			}
			$this->level = max(array_keys($LEVELS_REQUIREMENTS));
		}
		return $this->level;
	}

	public function getLevelName() {
		$level_name = Globals::getLevelRequirements()[$this->getLevelID()]['Name'];
		if ($this->isPresident()) {
			$level_name = '<img src="images/council_president.png" title="' . Globals::getRaceName($this->getRaceID()) . ' President" height="12" width="16" />&nbsp;' . $level_name;
		}
		return $level_name;
	}

	public function getMaxLevel() {
		return max(array_keys(Globals::getLevelRequirements()));
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

	public function getDisplayName($includeAlliance = false) {
		$return = get_colored_text($this->getAlignment(), $this->playerName . ' (' . $this->getPlayerID() . ')');
		if ($this->isNPC()) {
			$return .= ' <span class="npcColour">[NPC]</span>';
		}
		if ($includeAlliance) {
			$return .= ' (' . $this->getAllianceName() . ')';
		}
		return $return;
	}

	public function getBBLink() {
			return '[player=' . $this->getPlayerID() . ']';
	}

	public function getLinkedDisplayName($includeAlliance = true) {
		$return = '<a href="' . $this->getTraderSearchHREF() . '">' . $this->getDisplayName() . '</a>';
		if ($includeAlliance) {
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

	public static function getColouredRaceNameOrDefault($otherRaceID, AbstractSmrPlayer $player = null, $linked = false) {
		$relations = 0;
		if ($player !== null) {
			$relations = $player->getRelation($otherRaceID);
		}
		return Globals::getColouredRaceName($otherRaceID, $relations, $linked);
	}

	public function getColouredRaceName($otherRaceID, $linked = false) {
		return self::getColouredRaceNameOrDefault($otherRaceID, $this, $linked);
	}

	public function setRaceID($raceID) {
		if ($this->raceID == $raceID)
			return;
		$this->raceID = $raceID;
		$this->hasChanged = true;
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
		return $this->getAllianceID() != 0;
	}

	protected function setAllianceID($ID) {
		if ($this->allianceID == $ID)
			return;
		$this->allianceID = $ID;
		if ($this->allianceID != 0) {
			$status = $this->hasNewbieStatus() ? 'NEWBIE' : 'VETERAN';
			$this->db->query('INSERT IGNORE INTO player_joined_alliance (account_id,game_id,alliance_id,status) ' .
				'VALUES (' . $this->db->escapeNumber($this->getAccountID()) . ',' . $this->db->escapeNumber($this->getGameID()) . ',' . $this->db->escapeNumber($this->getAllianceID()) . ',' . $this->db->escapeString($status) . ')');
		}
		$this->hasChanged = true;
	}

	public function getAllianceBBLink() {
		return $this->hasAlliance() ? '[alliance=' . $this->getAllianceID() . ']' : $this->getAllianceName();
	}

	public function getAllianceName($linked = false, $includeAllianceID = false) {
		if ($this->hasAlliance()) {
			return $this->getAlliance()->getAllianceName($linked, $includeAllianceID);
		}
		else {
			return 'No Alliance';
		}
	}

	public function getAllianceRole($allianceID = false) {
		if ($allianceID === false) {
			$allianceID = $this->getAllianceID();
		}
		if (!isset($this->allianceRoles[$allianceID])) {
			$this->allianceRoles[$allianceID] = 0;
			$this->db->query('SELECT role_id
						FROM player_has_alliance_role
						WHERE ' . $this->SQL . '
						AND alliance_id=' . $this->db->escapeNumber($allianceID) . '
						LIMIT 1');
			if ($this->db->nextRecord()) {
				$this->allianceRoles[$allianceID] = $this->db->getInt('role_id');
			}
		}
		return $this->allianceRoles[$allianceID];
	}

	public function isCombatDronesKamikazeOnMines() {
		return $this->combatDronesKamikazeOnMines;
	}

	public function setCombatDronesKamikazeOnMines($bool) {
		if ($this->combatDronesKamikazeOnMines == $bool)
			return;
		$this->combatDronesKamikazeOnMines = $bool;
		$this->hasChanged = true;
	}

	protected abstract function getGadgetsData();
	public function getGadgets() {
		$this->getGadgetsData();
		return $this->gadgets;
	}

	public function getGadget($gadgetID) {
		if (!is_numeric($gadgetID)) {
			global $GADGETS;
			$gadgetID = $GADGETS[$gadgetID]['ID'];
		}
		$gadgets = $this->getGadgets();
		if (isset($gadgets[$gadgetID]))
			return $gadgets[$gadgetID];
		return false;
	}

	public function isGadgetEquipped($gadgetID) {
		$gadget = $this->getGadget($gadgetID);
		if ($gadget === false)
			return false;
		return $gadget['Equipped'] > 0 && $gadget['Equipped'] < TIME && ($gadget['Expires'] == 0 || $gadget['Expires'] > TIME) && $gadget['Cooldown'] <= TIME;
	}

	protected abstract function getPureRelationsData();

	public function getPureRelations() {
		$this->getPureRelationsData();
		return $this->pureRelations;
	}

	/**
	 * Get personal relations with a race
	 */
	public function getPureRelation($raceID) {
		$rels = $this->getPureRelations();
		return $rels[$raceID];
	}

	public function getRelations() {
		if (!isset($this->relations)) {
			//get relations
			$RACES = Globals::getRaces();
			$raceRelations = Globals::getRaceRelations($this->getGameID(), $this->getRaceID());
			$pureRels = $this->getPureRelations(); // make sure they're initialised.
			$this->relations = array();
			foreach ($RACES as $raceID => $raceName) {
				$this->relations[$raceID] = $pureRels[$raceID] + $raceRelations[$raceID];
			}
		}
		return $this->relations;
	}

	/**
	 * Get total relations with a race (personal + political)
	 */
	public function getRelation($raceID) {
		$rels = $this->getRelations();
		return $rels[$raceID];
	}

	abstract public function &getShip();

	public function &shootPlayer(AbstractSmrPlayer $targetPlayer) {
		return $this->getShip()->shootPlayer($targetPlayer);
	}

	public function &shootForces(SmrForce $forces) {
		return $this->getShip()->shootForces($forces);
	}

	public function &shootPort(SmrPort $port) {
		return $this->getShip()->shootPort($port);
	}

	public function &shootPlanet(SmrPlanet $planet, $delayed) {
		return $this->getShip()->shootPlanet($planet, $delayed);
	}

	public function &shootPlayers(array $targetPlayers) {
		return $this->getShip()->shootPlayers($targetPlayers);
	}

	public function getMilitaryPayment() {
		return $this->militaryPayment;
	}

	public function hasMilitaryPayment() {
		return $this->getMilitaryPayment() > 0;
	}

	public function setMilitaryPayment($amount) {
		if ($this->militaryPayment == $amount)
			return;
		$this->militaryPayment = $amount;
		$this->hasChanged = true;
	}

	public function increaseMilitaryPayment($amount) {
		if ($amount < 0)
			throw new Exception('Trying to increase negative military payment.');
		$this->setMilitaryPayment($this->getMilitaryPayment() + $amount);
	}

	public function decreaseMilitaryPayment($amount) {
		if ($amount < 0)
			throw new Exception('Trying to decrease negative military payment.');
		$this->setMilitaryPayment($this->getMilitaryPayment() - $amount);
	}

	abstract protected function getBountiesData();

	public function getBounties() : array {
		$this->getBountiesData();
		return $this->bounties;
	}

	public function hasBounties() : bool {
		return count($this->getBounties()) > 0;
	}

	protected function getBounty(int $bountyID) {
		$bounties = $this->getBounties();
		return isset($bounties[$bountyID]) ? $bounties[$bountyID] : false;
	}

	public function hasBounty(int $bountyID) : bool {
		return $this->getBounty($bountyID) !== false;
	}

	protected function getBountyAmount(int $bountyID) : int {
		$bounty = $this->getBounty($bountyID);
		return $bounty['Amount'];
	}

	protected function createBounty(string $type) : array {
		$bounty = array('Amount' => 0,
						'SmrCredits' => 0,
						'Type' => $type,
						'Claimer' => 0,
						'Time' => TIME,
						'ID' => $this->getNextBountyID(),
						'New' => true);
		$this->setBounty($bounty);
		return $bounty;
	}

	protected function getNextBountyID() : int {
		$keys = array_keys($this->getBounties());
		if (count($keys) > 0)
			return max($keys) + 1;
		else
			return 0;
	}

	protected function setBounty(array $bounty) : void {
		$this->bounties[$bounty['ID']] = $bounty;
		$this->hasBountyChanged[$bounty['ID']] = true;
	}

	protected function setBountyAmount(int $bountyID, int $amount) : void {
		$bounty = $this->getBounty($bountyID);
		$bounty['Amount'] = $amount;
		$this->setBounty($bounty);
	}

	public function increaseBountyAmount(int $bountyID, int $amount) : void {
		if ($amount < 0)
			throw new Exception('Trying to increase negative bounty.');
		$this->setBountyAmount($this->getBountyAmount($bountyID) + $amount);
	}

	public function decreaseBountyAmount(int $bountyID, int $amount) : void {
		if ($amount < 0)
			throw new Exception('Trying to decrease negative bounty.');
		$this->setBountyAmount($this->getBountyAmount($bountyID) + $amount);
	}

	public function getCurrentBounty(string $type) : array {
		$bounties = $this->getBounties();
		foreach ($bounties as $bounty) {
			if ($bounty['Claimer'] == 0 && $bounty['Type'] == $type)
				return $bounty;
		}
		return $this->createBounty($type);
	}

	public function hasCurrentBounty(string $type) : bool {
		$bounties = $this->getBounties();
		foreach ($bounties as $bounty) {
			if ($bounty['Claimer'] == 0 && $bounty['Type'] == $type)
				return true;
		}
		return false;
	}

	protected function getCurrentBountyAmount(string $type) : int {
		$bounty = $this->getCurrentBounty($type);
		return $bounty['Amount'];
	}

	protected function setCurrentBountyAmount(string $type, int $amount) : void {
		$bounty = $this->getCurrentBounty($type);
		if ($bounty['Amount'] == $amount)
			return;
		$bounty['Amount'] = $amount;
		$this->setBounty($bounty);
	}

	public function increaseCurrentBountyAmount(string $type, int $amount) : void {
		if ($amount < 0)
			throw new Exception('Trying to increase negative current bounty.');
		$this->setCurrentBountyAmount($type, $this->getCurrentBountyAmount($type) + $amount);
	}

	public function decreaseCurrentBountyAmount(string $type, int $amount) : void {
		if ($amount < 0)
			throw new Exception('Trying to decrease negative current bounty.');
		$this->setCurrentBountyAmount($type, $this->getCurrentBountyAmount($type) - $amount);
	}

	protected function getCurrentBountySmrCredits(string $type) : int {
		$bounty = $this->getCurrentBounty($type);
		return $bounty['SmrCredits'];
	}

	protected function setCurrentBountySmrCredits(string $type, int $credits) : void {
		$bounty = $this->getCurrentBounty($type);
		if ($bounty['SmrCredits'] == $credits)
			return;
		$bounty['SmrCredits'] = $credits;
		$this->setBounty($bounty);
	}

	public function increaseCurrentBountySmrCredits(string $type, int $credits) : void {
		if ($credits < 0)
			throw new Exception('Trying to increase negative current bounty.');
		$this->setCurrentBountySmrCredits($type, $this->getCurrentBountySmrCredits($type) + $credits);
	}

	public function decreaseCurrentBountySmrCredits(string $type, int $credits) : void {
		if ($credits < 0)
			throw new Exception('Trying to decrease negative current bounty.');
		$this->setCurrentBountySmrCredits($type, $this->getCurrentBountySmrCredits($type) - $credits);
	}

	public function setBountiesClaimable(AbstractSmrPlayer $claimer) : void {
		$bounties = $this->getBounties();
		if (is_array($bounties)) {
			foreach ($bounties as $bounty) {
				if ($bounty['Claimer'] == 0) {
					$bounty['Claimer'] = $claimer->getAccountID();
					$this->setBounty($bounty);
				}
			}
		}
	}


	abstract protected function getHOFData();

	public function getHOF(array $typeList = null) {
		$this->getHOFData();
		if ($typeList == null)
			return $this->HOF;
		$hof = $this->HOF;
		foreach ($typeList as $type) {
			if (!isset($hof[$type]))
				return 0;
			$hof = $hof[$type];
		}
		return $hof;
	}

	public function increaseHOF($amount, array $typeList, $visibility) {
		if ($amount < 0)
			throw new Exception('Trying to increase negative HOF: ' . implode(':', $typeList));
		if ($amount == 0)
			return;
		$this->setHOF($this->getHOF($typeList) + $amount, $typeList, $visibility);
	}

	public function decreaseHOF($amount, array $typeList, $visibility) {
		if ($amount < 0)
			throw new Exception('Trying to decrease negative HOF: ' . implode(':', $typeList));
		if ($amount == 0)
			return;
		$this->setHOF($this->getHOF($typeList) - $amount, $typeList, $visibility);
	}

	public function setHOF($amount, array $typeList, $visibility) {
		if (is_array($this->getHOF($typeList)))
			throw new Exception('Trying to overwrite a HOF type: ' . implode(':', $typeList));
		if ($this->isNPC()) {
			// Don't store HOF for NPCs.
			return;
		}
		if ($this->getHOF($typeList) == $amount)
			return;
		if ($amount < 0)
			$amount = 0;
		$this->getHOF();

		$hofType = implode(':', $typeList);
		if (!isset(self::$HOFVis[$hofType])) {
			self::$hasHOFVisChanged[$hofType] = self::HOF_NEW;
		}
		else if (self::$HOFVis[$hofType] != $visibility) {
			self::$hasHOFVisChanged[$hofType] = self::HOF_CHANGED;
		}
		self::$HOFVis[$hofType] = $visibility;

		$hof =& $this->HOF;
		$hofChanged =& $this->hasHOFChanged;
		$new = false;
		foreach ($typeList as $type) {
			if (!isset($hofChanged[$type]))
				$hofChanged[$type] = array();
			if (!isset($hof[$type])) {
				$hof[$type] = array();
				$new = true;
			}
			$hof =& $hof[$type];
			$hofChanged =& $hofChanged[$type];
		}
		if ($hofChanged == null) {
			$hofChanged = self::HOF_CHANGED;
			if ($new)
				$hofChanged = self::HOF_NEW;
		}
		$hof = $amount;
	}

	abstract public function killPlayer($sectorID);
	abstract public function &killPlayerByPlayer(AbstractSmrPlayer $killer);
	abstract public function &killPlayerByForces(SmrForce $forces);
	abstract public function &killPlayerByPort(SmrPort $port);
	abstract public function &killPlayerByPlanet(SmrPlanet $planet);


	public function getTurns() {
		return $this->turns;
	}

	public function hasTurns() {
		return $this->turns > 0;
	}

	public function getMaxTurns() {
		return $this->getGame()->getMaxTurns();
	}

	public function setTurns($turns) {
		if ($this->turns == $turns) {
			return;
		}
		// Make sure turns are in range [0, MaxTurns]
		$this->turns = max(0, min($turns, $this->getMaxTurns()));
		$this->hasChanged = true;
	}

	public function takeTurns($take, $takeNewbie = 0) {
		if ($take < 0 || $takeNewbie < 0) {
			throw new Exception('Trying to take negative turns.');
		}
		$take = ceil($take);
		// Only take up to as many newbie turns as we have remaining
		$takeNewbie = min($this->getNewbieTurns(), $takeNewbie);

		$this->setTurns($this->getTurns() - $take);
		$this->setNewbieTurns($this->getNewbieTurns() - $takeNewbie);
		$this->increaseHOF($take, array('Movement', 'Turns Used', 'Since Last Death'), HOF_ALLIANCE);
		$this->increaseHOF($take, array('Movement', 'Turns Used', 'Total'), HOF_ALLIANCE);
		$this->increaseHOF($takeNewbie, array('Movement', 'Turns Used', 'Newbie'), HOF_ALLIANCE);

		// Player has taken an action
		$this->setLastActive(TIME);
		$this->updateLastCPLAction();
	}

	public function giveTurns($give, $giveNewbie = 0) {
		if ($give < 0 || $giveNewbie < 0) {
			throw new Exception('Trying to give negative turns.');
		}
		$this->setTurns($this->getTurns() + floor($give));
		$this->setNewbieTurns($this->getNewbieTurns() + $giveNewbie);
	}

	public function getLastActive() {
		return $this->lastActive;
	}

	public function setLastActive($lastActive) {
		if ($this->lastActive == $lastActive)
			return;
		$this->lastActive = $lastActive;
		$this->hasChanged = true;
	}

	public function getLastCPLAction() {
		return $this->lastCPLAction;
	}

	public function setLastCPLAction($time) {
		if ($this->lastCPLAction == $time)
			return;
		$this->lastCPLAction = $time;
		$this->hasChanged = true;
	}

	public function updateLastCPLAction() {
		$this->setLastCPLAction(TIME);
	}

	public function getMissions() {
		if (!isset($this->missions)) {
			$this->db->query('SELECT * FROM player_has_mission WHERE ' . $this->SQL);
			$this->missions = array();
			while ($this->db->nextRecord()) {
				$missionID = $this->db->getInt('mission_id');
				$this->missions[$missionID] = array(
					'On Step' => $this->db->getInt('on_step'),
					'Progress' => $this->db->getInt('progress'),
					'Unread' => $this->db->getBoolean('unread'),
					'Expires' => $this->db->getInt('step_fails'),
					'Sector' => $this->db->getInt('mission_sector'),
					'Starting Sector' => $this->db->getInt('starting_sector')
				);
				$this->rebuildMission($missionID);
			}
		}
		return $this->missions;
	}

	public function getActiveMissions() {
		$missions = $this->getMissions();
		foreach ($missions as $missionID => $mission) {
			if ($mission['On Step'] >= count(MISSIONS[$missionID]['Steps'])) {
				unset($missions[$missionID]);
			}
		}
		return $missions;
	}

	protected function getMission($missionID) {
		$missions = $this->getMissions();
		if (isset($missions[$missionID]))
			return $missions[$missionID];
		return false;
	}

	protected function hasMission($missionID) {
		return $this->getMission($missionID) !== false;
	}

	protected function updateMission($missionID) {
		$this->getMissions();
		if (isset($this->missions[$missionID])) {
			$mission = $this->missions[$missionID];
			$this->db->query('
				UPDATE player_has_mission
				SET on_step = ' . $this->db->escapeNumber($mission['On Step']) . ',
					progress = ' . $this->db->escapeNumber($mission['Progress']) . ',
					unread = ' . $this->db->escapeBoolean($mission['Unread']) . ',
					starting_sector = ' . $this->db->escapeNumber($mission['Starting Sector']) . ',
					mission_sector = ' . $this->db->escapeNumber($mission['Sector']) . ',
					step_fails = ' . $this->db->escapeNumber($mission['Expires']) . '
				WHERE ' . $this->SQL . ' AND mission_id = ' . $this->db->escapeNumber($missionID) . ' LIMIT 1'
			);
			return true;
		}
		return false;
	}

	private function setupMissionStep($missionID) {
		$mission =& $this->missions[$missionID];
		if ($mission['On Step'] >= count(MISSIONS[$missionID]['Steps'])) {
			// Nothing to do if this mission is already completed
			return;
		}
		$step = MISSIONS[$missionID]['Steps'][$mission['On Step']];
		if (isset($step['PickSector'])) {
			$realX = Plotter::getX($step['PickSector']['Type'], $step['PickSector']['X'], $this->getGameID());
			if ($realX === false) {
				throw new Exception('Invalid PickSector definition in mission: ' . $missionID);
			}
			$path = Plotter::findDistanceToX($realX, $this->getSector(), true, null, $this);
			if ($path === false) {
				throw new Exception('Cannot find location: ' . $missionID);
			}
			$mission['Sector'] = $path->getEndSectorID();
		}
	}

	/**
	 * Declining a mission will permanently hide it from the player
	 * by adding it in its completed state.
	 */
	public function declineMission($missionID) {
		$finishedStep = count(MISSIONS[$missionID]['Steps']);
		$this->addMission($missionID, $finishedStep);
	}

	public function addMission($missionID, $step = 0) {
		$this->getMissions();

		if (isset($this->missions[$missionID]))
			return;
		$sector = 0;

		$mission = array(
			'On Step' => $step,
			'Progress' => 0,
			'Unread' => true,
			'Expires' => (TIME + 86400),
			'Sector' => $sector,
			'Starting Sector' => $this->getSectorID()
		);

		$this->missions[$missionID] =& $mission;
		$this->setupMissionStep($missionID);
		$this->rebuildMission($missionID);

		$this->db->query('
			REPLACE INTO player_has_mission (game_id,account_id,mission_id,on_step,progress,unread,starting_sector,mission_sector,step_fails)
			VALUES ('.$this->db->escapeNumber($this->gameID) . ',' . $this->db->escapeNumber($this->accountID) . ',' . $this->db->escapeNumber($missionID) . ',' . $this->db->escapeNumber($mission['On Step']) . ',' . $this->db->escapeNumber($mission['Progress']) . ',' . $this->db->escapeBoolean($mission['Unread']) . ',' . $this->db->escapeNumber($mission['Starting Sector']) . ',' . $this->db->escapeNumber($mission['Sector']) . ',' . $this->db->escapeNumber($mission['Expires']) . ')'
		);
	}

	private function rebuildMission($missionID) {
		$mission = $this->missions[$missionID];
		$this->missions[$missionID]['Name'] = MISSIONS[$missionID]['Name'];

		if ($mission['On Step'] >= count(MISSIONS[$missionID]['Steps'])) {
			// If we have completed this mission just use false to indicate no current task.
			$currentStep = false;
		}
		else {
			$currentStep = MISSIONS[$missionID]['Steps'][$mission['On Step']];
			$currentStep['Text'] = str_replace(array('<Race>', '<Sector>', '<Starting Sector>', '<trader>'), array($this->getRaceID(), $mission['Sector'], $mission['Starting Sector'], $this->playerName), $currentStep['Text']);
			if (isset($currentStep['Task'])) {
				$currentStep['Task'] = str_replace(array('<Race>', '<Sector>', '<Starting Sector>', '<trader>'), array($this->getRaceID(), $mission['Sector'], $mission['Starting Sector'], $this->playerName), $currentStep['Task']);
			}
			if (isset($currentStep['Level'])) {
				$currentStep['Level'] = str_replace('<Player Level>', $this->getLevelID(), $currentStep['Level']);
			}
			else {
				$currentStep['Level'] = 0;
			}
		}
		$this->missions[$missionID]['Task'] = $currentStep;
	}

	public function deleteMission($missionID) {
		$this->getMissions();
		if (isset($this->missions[$missionID])) {
			unset($this->missions[$missionID]);
			$this->db->query('DELETE FROM player_has_mission WHERE ' . $this->SQL . ' AND mission_id = ' . $this->db->escapeNumber($missionID) . ' LIMIT 1');
			return true;
		}
		return false;
	}

	public function markMissionsRead() {
		$this->getMissions();
		$unreadMissions = array();
		foreach ($this->missions as $missionID => &$mission) {
			if ($mission['Unread']) {
				$unreadMissions[] = $missionID;
				$mission['Unread'] = false;
				$this->updateMission($missionID);
			}
		}
		return $unreadMissions;
	}

	public function claimMissionReward($missionID) {
		$this->getMissions();
		$mission =& $this->missions[$missionID];
		if ($mission === false) {
			throw new Exception('Unknown mission: ' . $missionID);
		}
		if ($mission['Task'] === false || $mission['Task']['Step'] != 'Claim') {
			throw new Exception('Cannot claim mission: ' . $missionID . ', for step: ' . $mission['On Step']);
		}
		$mission['On Step']++;
		$mission['Unread'] = true;
		foreach ($mission['Task']['Rewards'] as $rewardItem => $amount) {
			switch ($rewardItem) {
				case 'Credits':
					$this->increaseCredits($amount);
				break;
				case 'Experience':
					$this->increaseExperience($amount);
				break;
			}
		}
		$rewardText = $mission['Task']['Rewards']['Text'];
		if ($mission['On Step'] < count(MISSIONS[$missionID]['Steps'])) {
			// If we haven't finished the mission yet then 
			$this->setupMissionStep($missionID);
		}
		$this->rebuildMission($missionID);
		$this->updateMission($missionID);
		return $rewardText;
	}

	public function getAvailableMissions() {
		$availableMissions = array();
		foreach (MISSIONS as $missionID => $mission) {
			if ($this->hasMission($missionID)) {
				continue;
			}
			$realX = Plotter::getX($mission['HasX']['Type'], $mission['HasX']['X'], $this->getGameID());
			if ($realX === false) {
				throw new Exception('Invalid HasX definition in mission: ' . $missionID);
			}
			if ($this->getSector()->hasX($realX)) {
				$availableMissions[$missionID] = $mission;
			}
		}
		return $availableMissions;
	}

	public function actionTaken($actionID, array $values) {
		if (!in_array($actionID, MISSION_ACTIONS)) {
			throw new Exception('Unknown action: ' . $actionID);
		}
// TODO: Reenable this once tested.		if($this->getAccount()->isLoggingEnabled())
			switch ($actionID) {
				case 'WalkSector':
					$this->getAccount()->log(LOG_TYPE_MOVEMENT, 'Walks to sector: ' . $values['Sector']->getSectorID(), $this->getSectorID());
				break;
				case 'JoinAlliance':
					$this->getAccount()->log(LOG_TYPE_ALLIANCE, 'joined alliance: ' . $values['Alliance']->getAllianceName(), $this->getSectorID());
				break;
				case 'LeaveAlliance':
					$this->getAccount()->log(LOG_TYPE_ALLIANCE, 'left alliance: ' . $values['Alliance']->getAllianceName(), $this->getSectorID());
				break;
				case 'DisbandAlliance':
					$this->getAccount()->log(LOG_TYPE_ALLIANCE, 'disbanded alliance ' . $values['Alliance']->getAllianceName(), $this->getSectorID());
				break;
				case 'KickPlayer':
					$this->getAccount()->log(LOG_TYPE_ALLIANCE, 'kicked ' . $values['Player']->getAccount()->getLogin() . ' (' . $values['Player']->getPlayerName() . ') from alliance ' . $values['Alliance']->getAllianceName(), 0);
				break;
				case 'PlayerKicked':
					$this->getAccount()->log(LOG_TYPE_ALLIANCE, 'was kicked from alliance ' . $values['Alliance']->getAllianceName() . ' by ' . $values['Player']->getAccount()->getLogin() . ' (' . $values['Player']->getPlayerName() . ')', 0);
				break;

			}
		$this->getMissions();
		foreach ($this->missions as $missionID => &$mission) {
			if ($mission['Task'] !== false && $mission['Task']['Step'] == $actionID) {
				if (checkMissionRequirements($values, $mission, $this) === true) {
					$mission['On Step']++;
					$mission['Unread'] = true;
					$this->setupMissionStep($missionID);
					$this->rebuildMission($missionID);
					$this->updateMission($missionID);
				}
			}
		}
	}

	public function canSeeAny(array $otherPlayerArray) {
		foreach ($otherPlayerArray as $otherPlayer) {
			if ($this->canSee($otherPlayer)) {
				return true;
			}
		}
		return false;
	}

	public function canSee(AbstractSmrPlayer $otherPlayer) {
		if (!$otherPlayer->getShip()->isCloaked())
			return true;
		if ($this->sameAlliance($otherPlayer))
			return true;
		if ($this->getExperience() >= $otherPlayer->getExperience())
			return true;
		return false;
	}

	public function equals(AbstractSmrPlayer $otherPlayer = null) {
		return $otherPlayer !== null && $this->getAccountID() == $otherPlayer->getAccountID() && $this->getGameID() == $otherPlayer->getGameID();
	}

	public function sameAlliance(AbstractSmrPlayer $otherPlayer = null) {
		return $this->equals($otherPlayer) || (!is_null($otherPlayer) && $this->getGameID() == $otherPlayer->getGameID() && $this->hasAlliance() && $this->getAllianceID() == $otherPlayer->getAllianceID());
	}

	public function sharedForceAlliance(AbstractSmrPlayer $otherPlayer = null) {
		return $this->sameAlliance($otherPlayer);
	}

	public function forceNAPAlliance(AbstractSmrPlayer $otherPlayer = null) {
		return $this->sameAlliance($otherPlayer);
	}

	public function planetNAPAlliance(AbstractSmrPlayer $otherPlayer = null) {
		return $this->sameAlliance($otherPlayer);
	}

	public function traderNAPAlliance(AbstractSmrPlayer $otherPlayer = null) {
		return $this->sameAlliance($otherPlayer);
	}

	public function traderMAPAlliance(AbstractSmrPlayer $otherPlayer = null) {
		return $this->traderAttackTraderAlliance($otherPlayer) && $this->traderDefendTraderAlliance($otherPlayer);
	}

	public function traderAttackTraderAlliance(AbstractSmrPlayer $otherPlayer = null) {
		return $this->sameAlliance($otherPlayer);
	}

	public function traderDefendTraderAlliance(AbstractSmrPlayer $otherPlayer = null) {
		return $this->sameAlliance($otherPlayer);
	}

	public function traderAttackForceAlliance(AbstractSmrPlayer $otherPlayer = null) {
		return $this->sameAlliance($otherPlayer);
	}

	public function traderAttackPortAlliance(AbstractSmrPlayer $otherPlayer = null) {
		return $this->sameAlliance($otherPlayer);
	}

	public function traderAttackPlanetAlliance(AbstractSmrPlayer $otherPlayer = null) {
		return $this->sameAlliance($otherPlayer);
	}

	public function meetsAlignmentRestriction($restriction) {
		if ($restriction < 0)
			return $this->getAlignment() <= $restriction;
		if ($restriction > 0)
			return $this->getAlignment() >= $restriction;
		return true;
	}

	// Get an array of goods that are visible to the player
	public function getVisibleGoods() {
		$goods = Globals::getGoods();
		$visibleGoods = array();
		foreach ($goods as $key => $good) {
			if ($this->meetsAlignmentRestriction($good['AlignRestriction'])) {
				$visibleGoods[$key] = $good;
			}
		}
		return $visibleGoods;
	}

	/**
	 * Will retrieve all visited sectors, use only when you are likely to check a large number of these
	 */
	public function hasVisitedSector($sectorID) {
		if (!isset($this->visitedSectors)) {
			$this->visitedSectors = array();
			$this->db->query('SELECT sector_id FROM player_visited_sector WHERE ' . $this->SQL);
			while ($this->db->nextRecord())
				$this->visitedSectors[$this->db->getField('sector_id')] = false;
		}
		return !isset($this->visitedSectors[$sectorID]);
	}

	public function getLeaveNewbieProtectionHREF() {
		return SmrSession::getNewHREF(create_container('leave_newbie_processing.php'));
	}

	public function getExamineTraderHREF() {
		$container = create_container('skeleton.php', 'trader_examine.php');
		$container['target'] = $this->getAccountID();
		return SmrSession::getNewHREF($container);
	}

	public function getAttackTraderHREF() {
		return Globals::getAttackTraderHREF($this->getAccountID());
	}

	public function getPlanetKickHREF() {
		$container = create_container('planet_kick_processing.php', 'trader_attack_processing.php');
		$container['account_id'] = $this->getAccountID();
		return SmrSession::getNewHREF($container);
	}

	public function getTraderSearchHREF() {
		$container = create_container('skeleton.php', 'trader_search_result.php');
		$container['player_id'] = $this->getPlayerID();
		return SmrSession::getNewHREF($container);
	}

	public function getAllianceRosterHREF() {
		return Globals::getAllianceRosterHREF($this->getAllianceID());
	}

	public function getToggleWeaponHidingHREF($ajax = false) {
		$container = create_container('skeleton.php', 'toggle_processing.php');
		$container['toggle'] = 'WeaponHiding';
		$container['AJAX'] = $ajax;
		return SmrSession::getNewHREF($container);
	}
}
