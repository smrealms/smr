<?php
require_once(get_file_loc('SmrPlayer.class.inc'));
class SmrPlanet {
	protected static $CACHE_PLANETS = array();

	const DAMAGE_NEEDED_FOR_DOWNGRADE_CHANCE = 70;
	const CHANCE_TO_DOWNGRADE = 15;
	const TIME_TO_CREDIT_BUST = 10800; // 3 hours
	const TIME_BEFORE_REPLACING_BREAKING_NEWS = 600; // 10 minutes

	protected static $maxBuildings;

	protected $db;

	protected $sectorID;
	protected $gameID;
	protected $planetName;
	protected $ownerID;
	protected $password;
	protected $shields;
	protected $drones;
	protected $credits;
	protected $bonds;
	protected $maturity;
	protected $stockpile;
	protected $buildings;
	protected $inhabitableTime;
	protected $currentlyBuilding;

	protected $hasChanged = false;
	protected $hasChangedStockpile = false;
	protected $hasChangedBuildings = array();
	protected $hasStoppedBuilding = array();
	protected $isNew = false;

	protected $delayedShieldsDelta = 0;
	protected $delayedCDsDelta = 0;

	public static function refreshCache() {
		foreach(self::$CACHE_PLANETS as $gameID => &$gamePlanets) {
			foreach($gamePlanets as $sectorID => &$planet) {
				$planet = self::getPlanet($gameID,$sectorID,true);
			}
		}
	}

	public static function clearCache() {
		self::$CACHE_PLANETS = array();
	}

	public static function savePlanets() {
		foreach(self::$CACHE_PLANETS as &$gamePlanets) {
			foreach($gamePlanets as &$planet) {
				$planet->update();
			}
		}
	}

	public static function &getPlanet($gameID,$sectorID,$forceUpdate = false) {
		if($forceUpdate || !isset(self::$CACHE_PLANETS[$gameID][$sectorID])) {
			$p = new SmrPlanet($gameID,$sectorID);
			return self::$CACHE_PLANETS[$gameID][$sectorID] =& $p;
		}
		return self::$CACHE_PLANETS[$gameID][$sectorID];
	}

	public static function &createPlanet($gameID,$sectorID) {
		if(!self::getPlanet($gameID,$sectorID)->exists()) {
			require_once(get_file_loc('SmrGame.class.inc'));
			$inhabitableTime = SmrGame::getGame($gameID)->getStartDate() + pow(mt_rand(45, 85), 3);

			// insert planet into db
			$db = new SmrMySqlDatabase();
			$db->query('INSERT INTO planet (game_id, sector_id, inhabitable_time)
				VALUES (' . $db->escapeNumber($gameID) . ', ' . $db->escapeNumber($sectorID) . ', ' . $db->escapeNumber($inhabitableTime) . ')');
		}
		return self::getPlanet($gameID,$sectorID,true);
		//		if(!isset(self::$CACHE_PLANETS[$gameID][$sectorID])) {
		//			$p = new SmrPlanet($gameID,$sectorID,true);
		//			self::$CACHE_PLANETS[$gameID][$sectorID] =& $p;
		//		}
		//		return self::$CACHE_PLANETS[$gameID][$sectorID];
	}

	public static function removePlanet($gameID,$sectorID) {
		$db = new SmrMySqlDatabase();
		$db->query('DELETE FROM planet
					WHERE game_id = ' . $db->escapeNumber($gameID) . ' AND sector_id = ' . $db->escapeNumber($sectorID));
		$db->query('DELETE FROM planet_has_cargo
					WHERE game_id = ' . $db->escapeNumber($gameID) . ' AND sector_id = ' . $db->escapeNumber($sectorID));
		$db->query('DELETE FROM planet_has_building
					WHERE game_id = ' . $db->escapeNumber($gameID) . ' AND sector_id = ' . $db->escapeNumber($sectorID));
		$db->query('DELETE FROM planet_is_building
					WHERE game_id = ' . $db->escapeNumber($gameID) . ' AND sector_id = ' . $db->escapeNumber($sectorID));
		self::$CACHE_PLANETS[$gameID][$sectorID] = null;
		unset(self::$CACHE_PLANETS[$gameID][$sectorID]);
	}

	protected function __construct($gameID,$sectorID,$create=false) {
		$this->db = new SmrMySqlDatabase();


		//	if ($result = next_record($sql)) {
		//		global $PLANET_TYPES, $PLANET_RESEARCH, $GOODS, $PRIMARY_BUILDINGS;
		//		$PLANET_BUILDINGS = Globals::getPlanetBuildings();
		//		//get rev planet buildings
		//		foreach ($PLANET_BUILDINGS as $building_id => $build_arr) $rev_build[$build_arr['Name']] = $building_id;
		//
		//
		//		$planet['Gal ID']					= $result['gal_id'];
		//		$planet['Last Interest Check']		= $result['last_interest_check'];
		//		$planet['Assets Frozen Until']		= $result['assets_frozen_until'];
		//		$planet['Population']				= $result['population'];
		//		$planet['Last Population Update']	= $result['last_population_update'];
		//		$planet['Last Cargo Update']		= $result['last_cargo_update'];
		//		$planet['Type ID']					= $result['planet_type'];
		//		$planet['Area']						= $result['area'];
		//		$planet['Metals']					= $result['metals'];
		//		$planet['Energy']					= $result['energy'];
		//		$planet['Shields Stored']			= $result['shields_storage'];
		//		$planet['Combat Drones Stored']		= $result['combat_drones_storage'];
		//		$planet['Next Destroy']				= $result['next_destroy'];
		//		$planet['Defined']					= FALSE;
		//		$planet['Research Points']			= 16;
		//		$planet['Current Research'] 		= array();
		//
		//		//these will be adjusted later on in the function
		//		$planet['Area Available'] = $planet['Area'];
		//		$planet['Metals Available'] = $planet['Metals'];
		//		$planet['Energy Available'] = $planet['Energy'];
		//
		//		//get the possible buildings we can build, this is updated lower in the function
		//		$planet['Building Types Available'] = array();
		//		foreach($PLANET_BUILDINGS as $buildingID => $building)
		//			$planet['Building Types Available'][$buildingID] = 0;
		//		checkForPopIncrease($planet);
		//
		//		getOwnerInfo($planet);
		//		getGalaxyPlanets($planet);
		//
		//		getThisPlanetInfo($planet,$rev_build);
		//
		//
		//		//get the benefits
		//		$planet['Shield Recovery'] = $planet['Galaxy Buildings'][$rev_build['Shield Relay']] * 100;
		//		$planet['Drone Recovery'] = $planet['Galaxy Buildings'][$rev_build['Drone Control Tower']] * 20;
		//		//TODO: Get drone delay (30-45 min for lvl 70 is avg, 10 mins for lvl 25)
		//
		//
		//		checkForInterest($planet,$rev_build);
		//	}
		//	else {
		//		return FALSE;
		//	}
		//	return $planet;


		$this->db->query('SELECT * FROM planet WHERE game_id = ' . $this->db->escapeNumber($gameID) . ' AND sector_id = ' . $this->db->escapeNumber($sectorID));
		if ($this->db->nextRecord()) {
			$this->sectorID			= $this->db->getInt('sector_id');
			$this->gameID			= $this->db->getInt('game_id');
			$this->planetName		= stripslashes($this->db->getField('planet_name'));
			$this->ownerID			= $this->db->getInt('owner_id');
			$this->password			= $this->db->getField('password');
			$this->shields			= $this->db->getInt('shields');
			$this->drones			= $this->db->getInt('drones');
			$this->credits			= $this->db->getInt('credits');
			$this->bonds			= $this->db->getInt('bonds');
			$this->maturity			= $this->db->getInt('maturity');
			$this->inhabitableTime	= $this->db->getInt('inhabitable_time');

			$this->getStockpile();
			$this->getBuildings();
			$this->getCurrentlyBuilding();
		}
		//		else if($create) {
		//			$this->gameID		= (int)$gameID;
		//			$this->sectorID		= (int)$sectorID;
		//			$this->isNew		= true;
		//			return;
		//		}
		//		else {
		//			throw new Exception('No such sector: '.$gameID.'-'.$sectorID);
		//		}
		}

		public function getGameID() {
			return $this->gameID;
		}

		public function getSectorID() {
			return $this->sectorID;
		}

		public function &getGalaxy() {
			return SmrGalaxy::getGalaxyContaining($this->getGameID(),$this->getSectorID());
		}

		public function getOwnerID() {
			return $this->ownerID;
		}

		public function hasOwner() {
			return $this->ownerID!=0;
		}

		public function setOwnerID($claimerID) {
			if($this->ownerID==$claimerID) {
				return;
			}
			$this->ownerID=$claimerID;
			$this->hasChanged = true;
		}

		public function &getOwner() {
			return SmrPlayer::getPlayer($this->getOwnerID(),$this->getGameID());
		}

		public function getPassword() {
			return $this->password;
		}

		public function setPassword($password) {
			if($this->password==$password) {
				return;
			}
			$this->password=$password;
			$this->hasChanged = true;
		}

		public function removePassword() {
			$this->setPassword('');
		}

		public function getCredits() {
			return $this->credits;
		}

		public function setCredits($num) {
			if($this->credits==$num) {
				return;
			}
			if($num < 0) {
				throw new Exception('You cannot set negative credits.');
			}
			$this->credits = $num;
			$this->hasChanged = true;
		}

		public function increaseCredits($num) {
			if($num == 0) {
				return;
			}
			$this->setCredits($this->getCredits() + $num);
		}

		public function decreaseCredits($num) {
			if($num == 0) {
				return;
			}
			$this->setCredits($this->getCredits() - $num);
		}

		public function getMaturity() {
			return $this->maturity;
		}

		public function setMaturity($num) {
			if($this->maturity==$num) {
				return;
			}
			if($num < 0) {
				throw new Exception('You cannot set negative maturity.');
			}
			$this->maturity = $num;
			$this->hasChanged = true;
		}

		public function getBonds() {
			return $this->bonds;
		}

		public function setBonds($num) {
			if($this->bonds==$num) {
				return;
			}
			if($num < 0) {
				throw new Exception('You cannot set negative bonds.');
			}
			$this->bonds = $num;
			$this->hasChanged = true;
		}

		public function increaseBonds($num) {
			if($num == 0) {
				return;
			}
			$this->setBonds($this->getBonds() + $num);
		}

		public function decreaseBonds($num) {
			if($num == 0) {
				return;
			}
			$this->setBonds($this->getBonds() - $num);
		}

		public function checkForExcessDefense() {
			if ($this->getShields() > $this->getMaxShields()) {
				$this->setShields($this->getMaxShields());
			}
			if ($this->getCDs() > $this->getMaxCDs()) {
				$this->setCDs($this->getMaxCDs());
			}
		}

		public function getShields($delayed = false) {
			return $this->shields + ($delayed?$this->delayedShieldsDelta:0);
		}

		public function hasShields($delayed = false) {
			return $this->getShields($delayed)>0;
		}

		public function setShields($shields) {
			if($shields<0) {
				$shields=0;
			}
			if ($shields > $this->getMaxShields()) {
				$shields = $this->getMaxShields();
			}
			if($this->shields == $shields) {
				return;
			}
			$this->shields=$shields;
			$this->hasChanged = true;
		}

		public function decreaseShields($number, $delayed = false) {
			if($number == 0) {
				return;
			}
			if($delayed===false) {
				$this->setShields($this->getShields() - $number);
			}
			else {
				$this->delayedShieldsDelta -= $number;
			}
		}

		public function increaseShields($number, $delayed = false) {
			if($number == 0) {
				return;
			}
			if($delayed===false) {
				$this->setShields($this->getShields() + $number);
			}
			else {
				$this->delayedShieldsDelta += $number;
			}
		}

		public function getMaxShields() {
			return $this->getBuilding(PLANET_GENERATOR) * PLANET_GENERATOR_SHIELDS;
		}

		public function getCDs($delayed = false) {
			return $this->drones + ($delayed?$this->delayedCDsDelta:0);
		}

		public function hasCDs($delayed = false) {
			return $this->getCDs($delayed)>0;
		}

		public function setCDs($combatDrones) {
			if($combatDrones<0) {
				$combatDrones=0;
			}
			if ($combatDrones > $this->getMaxCDs()) {
				$combatDrones = $this->getMaxCDs();
			}
			if($this->drones == $combatDrones) {
				return;
			}
			$this->drones=$combatDrones;
			$this->hasChanged = true;
		}

		public function decreaseCDs($number, $delayed = false) {
			if($number == 0) {
				return;
			}
			if($delayed===false) {
				$this->setCDs($this->getCDs() - $number);
			}
			else {
				$this->delayedCDsDelta -= $number;
			}
		}

		public function increaseCDs($number, $delayed = false) {
			if($number == 0) {
				return;
			}
			if($delayed===false) {
				$this->setCDs($this->getCDs() + $number);
			}
			else {
				$this->delayedCDsDelta += $number;
			}
		}

		public function getMaxCDs() {
			return $this->getBuilding(PLANET_HANGAR) * PLANET_HANGAR_DRONES;
		}

		public function isDestroyed($delayed = false) {
			return !$this->hasCDs($delayed) && !$this->hasShields($delayed);
		}

		public function exists() {
			return $this->getGameID()!=null && $this->getSectorID()!==null;
		}

		public function getStockpile($goodID=false) {
			if(!isset($this->stockpile)) {
				// initialize cargo array
				$this->stockpile = array();
				// get supplies from db
				$this->db->query('SELECT good_id, amount FROM planet_has_cargo WHERE game_id = ' . $this->db->escapeNumber($this->getGameID()) . ' AND sector_id = ' . $this->db->escapeNumber($this->getSectorID()));
				// adding cargo and amount to array
				while ($this->db->nextRecord())
					$this->stockpile[$this->db->getInt('good_id')] = $this->db->getInt('amount');
			}
			if($goodID===false)
				return $this->stockpile;
			if(isset($this->stockpile[$goodID]))
				return $this->stockpile[$goodID];
			return 0;
		}

		public function hasStockpile($goodID=false) {
			return $this->getStockpile($goodID) > 0;
		}

		public function setStockpile($goodID,$amount) {
			if($amount < 0) {
				throw new Exception('Trying to set negative stockpile.');
			}
			if($this->getStockpile($goodID) == $amount) {
				return;
			}
			$this->stockpile[$goodID] = $amount;
			$this->hasChangedStockpile = true;
		}

		public function decreaseStockpile($goodID,$amount) {
			if($amount < 0)
				throw new Exception('Trying to decrease negative stockpile.');
			$this->setStockpile($goodID,$this->getStockpile($goodID) - $amount);
		}

		public function increaseStockpile($goodID,$amount) {
			if($amount < 0)
				throw new Exception('Trying to increase negative stockpile.');
			$this->setStockpile($goodID,$this->getStockpile($goodID) + $amount);
		}

		public function getBuildings() {
			if(!isset($this->buildings)) {
				$this->buildings = array();

				// get buildingss from db
				$this->db->query('SELECT construction_id, amount FROM planet_has_building WHERE game_id = ' . $this->db->escapeNumber($this->getGameID()) . ' AND sector_id = ' . $this->db->escapeNumber($this->getSectorID()));
				// adding building and amount to array
				while ($this->db->nextRecord()) {
					$this->buildings[$this->db->getInt('construction_id')] = $this->db->getInt('amount');
				}
			}
			return $this->buildings;
		}

		public function getBuilding($buildingTypeID) {
			$buildings = $this->getBuildings();
			if(isset($buildings[$buildingTypeID])) {
				return $buildings[$buildingTypeID];
			}
			return 0;
		}

		public function hasBuilding($buildingTypeID) {
			return $this->getBuilding($buildingTypeID)>0;
		}

		public function setBuilding($buildingTypeID,$number) {
			if($this->getBuilding($buildingTypeID) == $number)
				return;

			$this->buildings[$buildingTypeID] = $number;
			$this->hasChangedBuildings[$buildingTypeID] = true;
		}

		public function increaseBuilding($buildingTypeID,$number) {
			$this->setBuilding($buildingTypeID,$this->getBuilding($buildingTypeID)+$number);
		}

		public function destroyBuilding($buildingTypeID,$number) {
			if(!$this->hasBuilding($buildingTypeID)) {
				throw new Exception('Trying to destroy a nonexistent building');
			}
			$this->setBuilding($buildingTypeID,$this->getBuilding($buildingTypeID)-$number);
		}

		public function getTotalBuildings() {
			$totalBuildings = 0;
			foreach($this->getBuildings() as $building) {
				$totalBuildings += $building;
			}
			return $totalBuildings;
		}

		function getCurrentlyBuilding() {
			if(!isset($this->currentlyBuilding)) {
				$this->currentlyBuilding = array();
				$this->db->query('SELECT * FROM planet_is_building WHERE game_id = ' . $this->db->escapeNumber($this->getGameID()) . ' AND sector_id = ' . $this->db->escapeNumber($this->getSectorID()));
				while($this->db->nextRecord()) {
					if($this->db->getInt('time_complete') <= TIME) {
						$PLANET_BUILDINGS =& Globals::getPlanetBuildings();
						$expGain = $PLANET_BUILDINGS[$this->db->getInt('construction_id')]['ExpGain'];
						$player =& SmrPlayer::getPlayer($this->db->getInt('constructor_id'),$this->getGameID());
						$player->increaseHOF(1,array('Planet','Buildings','Built'), HOF_ALLIANCE);
						$player->increaseExperience($expGain);
						$player->increaseHOF($expGain,array('Planet','Buildings','Experience'), HOF_ALLIANCE);
						$this->hasStoppedBuilding[] = $this->db->getInt('building_slot_id');
						$this->increaseBuilding($this->db->getInt('construction_id'), 1);
					}
					else {
						$this->currentlyBuilding[$this->db->getInt('building_slot_id')] = array(
							'BuildingSlotID' => $this->db->getInt('building_slot_id'),
							'ConstructionID' => $this->db->getInt('construction_id'),
							'ConstructorID' => $this->db->getInt('constructor_id'),
							'Finishes' => $this->db->getInt('time_complete'),
							'TimeRemaining' => $this->db->getInt('time_complete') - TIME
						);
					}
				}
			}
			return $this->currentlyBuilding;
		}

		public static function getMaxBuildings($buildingTypeID=false) {
			if(!isset(self::$maxBuildings)) {
				self::$maxBuildings = array();

				$db = new SmrMySqlDatabase();
				// get buildingss from db
				$db->query('SELECT construction_id, max_construction FROM planet_construction');
				while ($this->db->nextRecord()) {
					self::$maxBuildings[$db->getInt('construction_id')] = $db->getInt('max_construction');
				}
			}
			if($buildingTypeID===false) {
				return self::$maxBuildings;
			}
			return self::$maxBuildings[$buildingTypeID];
			//			global $PRIMARY_BUILDINGS;
			//			$PLANET_BUILDINGS = Globals::getPlanetBuildings();
			//				if (isset($PRIMARY_BUILDINGS[$building_type_id]))
			//					$planet['Defined'] = TRUE;
			//
			//					if (isset($PLANET_BUILDINGS[$building_type_id]['Unlocks'])) {
			//						foreach ($PLANET_BUILDINGS[$building_type_id]['Unlocks'] as $building_id => $max_amount)
			//							$planet['Building Types Available'][$building_id] += $max_amount;
			//					}
			//
			//					//define planet
			//					if (isset($PRIMARY_BUILDINGS[$building_type_id])) {
			//						$planet['Type ID'] = $PRIMARY_BUILDINGS[$building_type_id];
			//						query('UPDATE planet SET planet_type = ' . $planet['Type ID'] . ' WHERE '.$planet['SQL'].' LIMIT 1');
			//					}
	}

	public function doDelayedUpdates() {
		$this->setShields($this->getShields(true));
		$this->delayedShieldsDelta = 0;
		$this->setCDs($this->getCDs(true));
		$this->delayedCDsDelta = 0;
	}

	public function update() {
		if(!$this->exists()) {
			return;
		}
		$this->doDelayedUpdates();
		if($this->hasChanged) {
			$this->db->query('UPDATE planet SET owner_id = ' . $this->db->escapeNumber($this->ownerID) . ',
									password = '.$this->db->escapeString($this->password) .',
									planet_name = ' . $this->db->escapeString($this->planetName) . ',
									shields = ' . $this->db->escapeNumber($this->shields) . ',
									drones = ' . $this->db->escapeNumber($this->drones) . ',
									credits = ' . $this->db->escapeNumber($this->credits) . ',
									bonds = ' . $this->db->escapeNumber($this->bonds) . ',
									maturity = ' . $this->db->escapeNumber($this->maturity) . '
								WHERE game_id = ' . $this->db->escapeNumber($this->gameID) . '
									AND sector_id = ' . $this->db->escapeNumber($this->sectorID));
			$this->hasChanged = false;
		}

		if($this->hasChangedStockpile) {
			// write stockpile info
			foreach ($this->getStockpile() as $id => $amount) {
				if ($amount != 0) {
					$this->db->query('REPLACE INTO planet_has_cargo (game_id, sector_id, good_id, amount) ' .
										 'VALUES(' . $this->db->escapeNumber($this->getGameID()) . ', ' . $this->db->escapeNumber($this->getSectorID()) . ', ' . $this->db->escapeNumber($id) . ', ' . $this->db->escapeNumber($amount) . ')');
				}
				else {
					$this->db->query('DELETE FROM planet_has_cargo WHERE game_id = ' . $this->db->escapeNumber($this->getGameID()) . '
										AND sector_id = ' . $this->db->escapeNumber($this->getSectorID()) . '
										AND good_id = ' . $this->db->escapeNumber($id));
				}
			}
		}


		if(count($this->hasStoppedBuilding)>0) {
			$this->db->query('DELETE FROM planet_is_building
							WHERE game_id = ' . $this->db->escapeNumber($this->getGameID()) . '
								AND sector_id = ' . $this->db->escapeNumber($this->getSectorID()) . '
								AND building_slot_id IN (' . $this->db->escapeArray($this->hasStoppedBuilding) . ') LIMIT ' . count($this->hasStoppedBuilding));
			$this->hasStoppedBuilding = array();
		}
		// write building info
		foreach ($this->hasChangedBuildings as $id => $hasChanged) {
			if($hasChanged===true) {
				if ($this->hasBuilding($id)) {
					$this->db->query('REPLACE INTO planet_has_building (game_id, sector_id, construction_id, amount) ' .
										'VALUES(' . $this->db->escapeNumber($this->gameID) . ', ' . $this->db->escapeNumber($this->sectorID) . ', ' . $this->db->escapeNumber($id) . ', ' . $this->db->escapeNumber($this->getBuilding($id)) . ')');
				}
				else {
					$this->db->query('DELETE FROM planet_has_building
									WHERE game_id = ' . $this->db->escapeNumber($this->gameID) . '
										AND sector_id = ' . $this->db->escapeNumber($this->sectorID) . '
										AND construction_id = ' . $this->db->escapeNumber($id));
				}
				$this->hasChangedBuildings[$id] = false;
			}
		}
	}

	function getLevel() {
		return ($this->getBuilding(PLANET_GENERATOR) + $this->getBuilding(PLANET_HANGAR) + $this->getBuilding(PLANET_TURRET)) / 3;
	}

	function accuracy() {
		if($this->hasWeapons()) {
			$weapons =& $this->getWeapons();
			return $weapons[0]->getModifiedPlanetAccuracy($this);
		}
		return 0;
	}

	function getRemainingStockpile($id) {
		return 600 - $this->getStockpile($id);
	}

	/**
	 * Returns true if there is a building in progress
	 */
	function hasCurrentlyBuilding() {
		return count($this->getCurrentlyBuilding()) > 0;
	}

	function canBuild(AbstractSmrPlayer &$constructor, $constructionID) {
		if($this->hasCurrentlyBuilding()) {
			return 'There is already a building in progress!';
		}
		if($this->getBuilding($constructionID) >= self::getMaxBuildings($constructionID)) {
			return 'This planet has reached the maximum buildings of that type.';
		}
		$PLANET_BUILDINGS =& Globals::getPlanetBuildings();
		if($constructor->getCredits() < $PLANET_BUILDINGS[$constructionID]['Credit Cost']) {
			return 'You do not have enough credits.';
		}
		// take the goods that are needed
		foreach($PLANET_BUILDINGS[$constructionID]['Goods'] as $goodID => $amount) {
			if ($this->getStockpile($goodID) < $amount) {
				return 'There are not enough goods available.';
			}
		}
		return true;
	}

	function startBuilding(AbstractSmrPlayer &$constructor,$constructionID) {
		if(($message = $this->canBuild($constructor, $constructionID)) !== true) {
			throw new Exception('Unable to start building: '.$message);
		}
		$PLANET_BUILDINGS =& Globals::getPlanetBuildings();
		$constructor->decreaseCredits($PLANET_BUILDINGS[$constructionID]['Credit Cost']);
		// take the goods that are needed
		foreach($PLANET_BUILDINGS[$constructionID]['Goods'] as $goodID => $amount) {
			$this->decreaseStockpile($goodID,$amount);
		}

		// gets the time for the buildings
		$timeComplete = TIME + round($PLANET_BUILDINGS[$constructionID]['Build Time'] / Globals::getGameSpeed($this->getGameID()));

		$this->db->query('INSERT INTO planet_is_building (game_id, sector_id, construction_id, constructor_id, time_complete) ' .
						'VALUES (' . $this->db->escapeNumber($this->getGameID()) . ', ' . $this->db->escapeNumber($this->getSectorID()) . ', ' . $this->db->escapeNumber($constructionID) . ', ' . $this->db->escapeNumber($constructor->getAccountID()) . ',' . $this->db->escapeNumber($timeComplete) . ')');

		$this->currentlyBuilding[$this->db->getInsertID()] = array(
			'BuildingSlotID' => $this->db->getInsertID(),
			'ConstructionID' => $constructionID,
			'ConstructorID' => $constructor->getAccountID(),
			'Finishes' => $timeComplete,
			'TimeRemaining' => $timeComplete - TIME
		);
	}

	function stopBuilding($constructionID) {
		$currentlyBuilding = $this->getCurrentlyBuilding();
		$matchingBuilding = false;
		$latestFinish = 0;
		foreach($currentlyBuilding as $key => $building) {
			if($building['ConstructionID'] == $constructionID && $building['Finishes'] > $latestFinish) {
				$latestFinish = $building['Finishes'];
				$matchingBuilding = $building;
			}
		}
		if($matchingBuilding) {
			$this->hasStoppedBuilding[] = $matchingBuilding['BuildingSlotID'];
			unset($this->currentlyBuilding[$matchingBuilding['BuildingSlotID']]);
			return true;
		}
		return false;
	}

	public function getName() {
		return $this->planetName;
	}

	public function setName($name) {
		if($this->planetName == $name) {
			return;
		}
		$this->planetName = $name;
		$this->hasChanged = true;
	}

	public function getDisplayName() {
		return '<span style="color:yellow;font-variant:small-caps">' . $this->getName() . '(#'.$this->getSectorID().')</span>';;
	}

	public function isInhabitable() {
		return $this->inhabitableTime<= TIME;
	}

	public function isClaimed() {
		return $this->ownerID>0;
	}

	public function removeClaimed() {
		$this->setOwnerID(0);
	}

	public function getExamineHREF() {
		return SmrSession::getNewHREF(create_container('skeleton.php', 'planet_examine.php'));
	}

	public function getLandHREF() {
		return SmrSession::getNewHREF(create_container('planet_land_processing.php'));
	}

	public function getAttackHREF() {
		return SmrSession::getNewHREF(create_container('planet_attack_processing.php'));
	}


	public function attackedBy(AbstractSmrPlayer $trigger, array $attackers) {
		$trigger->increaseHOF(1,array('Combat','Planet','Number Of Triggers'), HOF_PUBLIC);
		foreach ($attackers as $attacker) {
			$attacker->increaseHOF(1,array('Combat','Planet','Number Of Attacks'), HOF_PUBLIC);
			$this->db->query('REPLACE INTO player_attacks_planet (game_id, account_id, sector_id, time, level) VALUES ' .
					'(' . $this->db->escapeNumber($this->getGameID()) . ', ' . $this->db->escapeNumber($attacker->getAccountID()) . ', ' . $this->db->escapeNumber($this->getSectorID()) . ', ' . $this->db->escapeNumber(TIME) . ', ' . $this->db->escapeNumber($this->getLevel()) . ')');
		}

		$this->db->query('SELECT 1 FROM news WHERE type = \'BREAKING\' AND game_id = ' . $this->db->escapeNumber($trigger->getGameID()) . ' AND time > ' . $this->db->escapeNumber(TIME - self::TIME_BEFORE_REPLACING_BREAKING_NEWS) . ' LIMIT 1');
		if ($this->db->getNumRows()==0) {
			if (count($attackers) >= 5) {
				$owner =& $this->getOwner();
				$text = count($attackers) . ' members of '.$trigger->getAllianceBBLink().' have been spotted attacking ' .
					$this->getDisplayName() . ' in sector #' . $this->getSectorID() . '. The planet is owned by ' . $owner->getBBLink();
				if ($owner->hasAlliance()) {
					$text .= ', a member of ' . $owner->getAllianceBBLink();
				}
				$text .= '.';
				$this->db->query('INSERT INTO news (game_id, time, news_message, type,killer_id,killer_alliance,dead_id,dead_alliance) VALUES (' . $this->db->escapeNumber($this->getGameID()) . ', ' . $this->db->escapeNumber(TIME) . ', ' . $this->db->escapeString($text) . ', \'BREAKING\',' . $this->db->escapeNumber($trigger->getAccountID()) . ',' . $this->db->escapeNumber($trigger->getAllianceID()) . ',' . $this->db->escapeNumber($owner->getAccountID()) . ',' . $this->db->escapeNumber($owner->getAllianceID()) . ')');
			}
		}
	}

	public function &getPlayers() {
		return SmrPlayer::getPlanetPlayers($this->getGameID(),$this->getSectorID());
	}

	public function hasPlayers() {
		return count($this->getPlayers()) > 0;
	}

	public function &getOtherTraders(AbstractSmrPlayer &$player) {
		$players = SmrPlayer::getPlanetPlayers($this->getGameID(),$this->getSectorID()); //Do not use & because we unset something and only want that in what we return
		unset($players[$player->getAccountID()]);
		return $players;
	}

	public function hasOtherTraders(AbstractSmrPlayer &$player) {
		return count($this->getOtherTraders($player)) > 0;
	}

	public function hasEnemyTraders(AbstractSmrPlayer &$player) {
		if(!$this->hasOtherTraders($player))
			return false;
		$otherPlayers =& $this->getOtherTraders($player);
		foreach($otherPlayers as &$otherPlayer) {
			if(!$player->traderNAPAlliance($otherPlayer))
				return true;
		} unset($otherPlayer);
		return false;
	}

	public function hasFriendlyTraders(AbstractSmrPlayer &$player) {
		if(!$this->hasOtherTraders($player))
			return false;
		$otherPlayers =& $this->getOtherTraders($player);
		foreach($otherPlayers as &$otherPlayer) {
			if($player->traderNAPAlliance($otherPlayer))
				return true;
		} unset($otherPlayer);
		return false;
	}

	public function &getWeapons() {
		$weapons = array();
		for($i=0;$i<$this->getBuilding(PLANET_TURRET);++$i) {
			$weapons[$i] =& SmrWeapon::getWeapon(Globals::getGameType($this->getGameID()),WEAPON_PLANET_TURRET);
		}
		return $weapons;
	}

	public function hasWeapons() {
		return count($this->getWeapons())>0;
	}

	public function &shootPlayer(AbstractSmrPlayer &$targetPlayer) {
		return $this->shootPlayers(array($targetPlayer));
	}

	public function &shootPlayers(array &$targetPlayers) {
		$results = array('Planet' => &$this, 'TotalDamage' => 0, $results['TotalDamagePerTargetPlayer'] = array());
		foreach($targetPlayers as &$targetPlayer) {
			$results['TotalDamagePerTargetPlayer'][$targetPlayer->getAccountID()] = 0;
		} unset($targetPlayer);
		if($this->isDestroyed()) {
			$results['DeadBeforeShot'] = true;
			return $results;
		}
		$results['DeadBeforeShot'] = false;
		$weapons =& $this->getWeapons();
		foreach($weapons as $orderID => &$weapon) {
			$results['Weapons'][$orderID] =& $weapon->shootPlayerAsPlanet($this, $targetPlayers[array_rand($targetPlayers)]);
			if($results['Weapons'][$orderID]['Hit']) {
				$results['TotalDamage'] += $results['Weapons'][$orderID]['ActualDamage']['TotalDamage'];
				$results['TotalDamagePerTargetPlayer'][$results['Weapons'][$orderID]['TargetPlayer']->getAccountID()] += $results['Weapons'][$orderID]['ActualDamage']['TotalDamage'];
			}
		}
		if($this->hasCDs()) {
			$thisCDs = new SmrCombatDrones($this->getGameID(),$this->getCDs(),true);
			$results['Drones'] =& $thisCDs->shootPlayerAsPlanet($this, $targetPlayers[array_rand($targetPlayers)]);
			$results['TotalDamage'] += $results['Drones']['ActualDamage']['TotalDamage'];
			$results['TotalDamagePerTargetPlayer'][$results['Drones']['TargetPlayer']->getAccountID()] += $results['Drones']['ActualDamage']['TotalDamage'];
		}
		return $results;
	}

	function &checkForDowngrade($damage) {
		$results = '';
		// Chance of planetary structure damage = For every 70 damage there is a 15% chance of destroying a structure.
		// Turrets have a 1 in 6 chance of being destroyed
		// Hangers have a 2 in 6 chance of being destroyed
		// Generators 3 in 6 chance of being destroyed
		//iterate over all chances
		for ($i = 0; $damage > self::DAMAGE_NEEDED_FOR_DOWNGRADE_CHANCE; $damage-=self::DAMAGE_NEEDED_FOR_DOWNGRADE_CHANCE) {
			//15% chance to destroy something
			if (mt_rand(1, 100) <= self::CHANCE_TO_DOWNGRADE) {
				$rand = mt_rand(1, 6);
				switch ($rand) {
					case 1:
						//destroy a turret
						if ($this->hasBuilding(PLANET_TURRET)) {
							$results .= 'This team destroys <span style = "color:red;">1</span> turret.<br />';
							$this->destroyBuilding(PLANET_TURRET, 1);
							break;
						}
						//if no turrets we fall through
					case 2:
					case 3:
						//destroy a hangar
						if ($this->hasBuilding(PLANET_HANGAR)) {
							$results .= 'This team destroys <span style ="color:red;">1</span> hangar.<br />';
							$this->destroyBuilding(PLANET_HANGAR, 1);
							$this->checkForExcessDefense();
							break;
						}
						//if no hangars we fall through
					case 4:
					case 5:
					case 6:
						//destroy a gen
						if ($this->hasBuilding(PLANET_GENERATOR)) {
							$results .= 'This team destroys <span style ="color:red;">1</span> generator.<br />';
							$this->destroyBuilding(PLANET_GENERATOR, 1);
							$this->checkForExcessDefense();
							break;
						}
						//if no gens then we fall through
					default:
						//very rare that we will not have a gen to destroy.
						$results .= 'A planetary structure barely survived the onslaught.<br />';
					break;
				}
			}
		}
		return $results;
	}

	public function &doWeaponDamage(array $damage, $delayed) {
		$alreadyDead = $this->isDestroyed(true);
		$shieldDamage = 0;
		$cdDamage = 0;
		$armourDamage = 0;
		if(!$alreadyDead) {
			if($damage['Shield'] || !$this->hasShields(true)) {
				$shieldDamage = $this->doShieldDamage(min($damage['MaxDamage'],$damage['Shield']),$delayed);
				$damage['MaxDamage'] -= $shieldDamage;
				if(!$this->hasShields(true) && ($shieldDamage == 0 || $damage['Rollover'])) {
					$cdDamage = $this->doCDDamage(min($damage['MaxDamage'],$damage['Armour']),$delayed);
					$damage['Armour'] -= $cdDamage;
					$damage['MaxDamage'] -= $cdDamage;
				}
			}
			else { //hit drones behind shields
				$cdDamage = $this->doCDDamage(floor(min($damage['MaxDamage'],$damage['Armour'])*DRONES_BEHIND_SHIELDS_DAMAGE_PERCENT),$delayed);
			}
		}

		$return = array(
					'KillingShot' => !$alreadyDead && $this->isDestroyed(true),
					'TargetAlreadyDead' => $alreadyDead,
					'Shield' => $shieldDamage,
					'HasShields' => $this->hasShields(true),
					'CDs' => $cdDamage,
					'NumCDs' => $cdDamage/CD_ARMOUR,
					'HasCDs' => $this->hasCDs(true),
					'TotalDamage' => $shieldDamage+$cdDamage+$armourDamage
		);
		return $return;
	}

	protected function doShieldDamage($damage, $delayed) {
		$actualDamage = min($this->getShields(true),$damage);
		$this->decreaseShields($actualDamage, $delayed);
		return $actualDamage;
	}

	protected function doCDDamage($damage, $delayed) {
		$actualDamage = min($this->getCDs(true),floor($damage/CD_ARMOUR));
		$this->decreaseCDs($actualDamage, $delayed);
		return $actualDamage*CD_ARMOUR;
	}

	public function creditCurrentAttackersForKill() {
		//get all players involved for HoF
		$this->db->query('SELECT account_id,level FROM player_attacks_planet WHERE game_id = ' . $this->db->escapeNumber($this->getGameID()) . ' AND sector_id = ' . $this->db->escapeNumber($this->getSectorID()) . ' AND time > ' . $this->db->escapeNumber(TIME - self::TIME_TO_CREDIT_BUST));
		while ($this->db->nextRecord()) {
			$currPlayer =& SmrPlayer::getPlayer($this->db->getInt('account_id'),$this->getGameID());
			$currPlayer->increaseHOF($this->db->getInt('level'),array('Combat','Planet','Levels'), HOF_PUBLIC);
			$currPlayer->increaseHOF(1,array('Combat','Planet','Completed'), HOF_PUBLIC);
		}
		$this->db->query('DELETE FROM player_attacks_planet WHERE sector_id = ' . $this->db->escapeNumber($this->getSectorID()) . ' AND game_id = ' . $this->db->escapeNumber($this->getGameID()));
	}

	public function &killPlanetByPlayer(AbstractSmrPlayer &$killer) {
		$return = array();
		$this->creditCurrentAttackersForKill();

		//kick everyone from planet
		$this->db->query('UPDATE player SET land_on_planet = \'FALSE\' WHERE sector_id = ' . $this->db->escapeNumber($this->getSectorID()) . ' AND game_id = ' . $this->db->escapeNumber($this->getGameID()));
		$this->removeClaimed();
		$this->removePassword();
		return $return;
	}
}

?>