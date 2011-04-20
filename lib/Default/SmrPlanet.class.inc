<?php
require_once(get_file_loc('SmrPlayer.class.inc'));
class SmrPlanet
{
	protected static $CACHE_PLANETS = array();

	const DAMAGE_NEEDED_FOR_DOWNGRADE_CHANCE = 70;
	const CHANCE_TO_DOWNGRADE = 15;

	protected $db;

	protected $sector_id;
	protected $game_id;
	protected $planet_name;
	protected $owner_id;
	protected $password;
	protected $shields;
	protected $drones;
	protected $credits;
	protected $bonds;
	var $maturity;
	protected $stockpile;
	protected $buildings;
	var $max_construction;
	var $inhabitable_time;
	var $level;
	protected $currentlyBuilding;

	protected $hasChangedStockpile = false;
	protected $isNew = false;

	protected $delayedShieldsDelta = 0;
	protected $delayedCDsDelta = 0;

	public static function refreshCache()
	{
		foreach(self::$CACHE_PLANETS as $gameID => &$gamePlanets)
		{
			foreach($gamePlanets as $sectorID => &$planets)
			{
				$planets = self::getPlanet($gameID,$sectorID,true);
			}
		}
	}
	
	public static function clearCache()
	{
		self::$CACHE_PLANETS = array();
	}

	public static function &getPlanet($gameID,$sectorID,$forceUpdate = false)
	{
		if($forceUpdate || !isset(self::$CACHE_PLANETS[$gameID][$sectorID]))
		{
			$p = new SmrPlanet($gameID,$sectorID);
			return self::$CACHE_PLANETS[$gameID][$sectorID] =& $p;
		}
		return self::$CACHE_PLANETS[$gameID][$sectorID];
	}

	public static function &createPlanet($gameID,$sectorID)
	{
		if(!self::getPlanet($gameID,$sectorID)->exists())
		{
			require_once(get_file_loc('SmrGame.class.inc'));
			$inhabitableTime = SmrGame::getGame($gameID)->getStartDate() + pow(mt_rand(45, 85), 3);
				
			// insert planet into db
			$db = new SmrMySqlDatabase();
			$db->query('INSERT INTO planet (game_id, sector_id, inhabitable_time) ' .
				'VALUES (' . $gameID . ', '.$sectorID.', '.$inhabitableTime.')');
		}
		return self::getPlanet($gameID,$sectorID,true);
		//		if(!isset(self::$CACHE_PLANETS[$gameID][$sectorID]))
		//		{
		//			$p = new SmrPlanet($gameID,$sectorID,true);
		//			self::$CACHE_PLANETS[$gameID][$sectorID] =& $p;
		//		}
		//		return self::$CACHE_PLANETS[$gameID][$sectorID];
	}

	public static function removePlanet($gameID,$sectorID)
	{
		$db = new SmrMySqlDatabase();
		$db->query('DELETE FROM planet WHERE game_id = '.$gameID.' AND ' .
									'sector_id = '.$sectorID);
		$db->query('DELETE FROM planet_has_cargo WHERE game_id = '.$gameID.' AND ' .
									'sector_id = '.$sectorID);
		$db->query('DELETE FROM planet_has_building WHERE game_id = '.$gameID.' AND ' .
									'sector_id = '.$sectorID);
		$db->query('DELETE FROM planet_is_building WHERE game_id = '.$gameID.' AND ' .
									'sector_id = '.$sectorID);
		self::$CACHE_PLANETS[$gameID][$sectorID] = null;
		unset(self::$CACHE_PLANETS[$gameID][$sectorID]);
	}

	protected function __construct($gameID,$sectorID,$create=false)
	{
		$this->db = new SmrMySqlDatabase();


		//	if ($result = next_record($sql))
		//	{
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
		//	else
		//	{
		//		return FALSE;
		//	}
		//	return $planet;


		$this->db->query('SELECT * FROM planet WHERE game_id = '.$gameID.' AND ' .
											'sector_id = '.$sectorID);
		if ($this->db->nextRecord())
		{
			$this->sector_id		= $this->db->getField('sector_id');
			$this->game_id			= $this->db->getField('game_id');
			$this->planet_name		= stripslashes($this->db->getField('planet_name'));
			$this->owner_id			= $this->db->getField('owner_id');
			$this->password			= $this->db->getField('password');
			$this->shields			= $this->db->getField('shields');
			$this->drones			= $this->db->getField('drones');
			$this->credits			= $this->db->getField('credits');
			$this->bonds			= $this->db->getField('bonds');
			$this->maturity			= $this->db->getField('maturity');
			$this->inhabitable_time = $this->db->getField('inhabitable_time');

			$this->getStockpile();
			$this->getBuildings();
			$this->getCurrentlyBuilding();
			$this->getMaxBuildings();
			$this->level = $this->getLevel();
		}
		//		else if($create)
		//		{
		//			$this->gameID		= (int)$gameID;
		//			$this->sectorID		= (int)$sectorID;
		//			$this->isNew		= true;
		//			return;
		//		}
		//		else
		//		{
		//			throw new Exception('No such sector: '.$gameID.'-'.$sectorID);
		//		}
		}

		public function getGameID()
		{
			return $this->game_id;
		}

		public function getSectorID()
		{
			return $this->sector_id;
		}
		
		public function &getGalaxy()
		{
			return SmrGalaxy::getGalaxyContaining($this->getGameID(),$this->getSectorID());
		}

		public function getOwnerID()
		{
			return $this->owner_id;
		}

		public function hasOwner()
		{
			return $this->owner_id!=0;
		}

		public function setOwnerID($claimerID)
		{
			$this->owner_id=$claimerID;
		}

		public function &getOwner()
		{
			return SmrPlayer::getPlayer($this->getOwnerID(),$this->getGameID());
		}

		public function getPassword()
		{
			return $this->password;
		}

		public function setPassword($password)
		{
			$this->password=$password;
		}

		public function removePassword()
		{
			$this->setPassword('');
		}

		public function getCredits()
		{
			return $this->credits;
		}

		public function setCredits($num)
		{
			if($this->credits==$num)
			{
				return;
			}
			if($num < 0)
			{
				throw new Exception('You cannot set negative credits.');
			}
			$this->credits = $num;
		}

		public function increaseCredits($num)
		{
			if($num == 0)
			{
				return;
			}
			$this->setCredits($this->getCredits() + $num);
		}

		public function decreaseCredits($num)
		{
			if($num == 0)
			{
				return;
			}
			$this->setCredits($this->getCredits() - $num);
		}

		public function getBonds()
		{
			return $this->bonds;
		}

		public function setBonds($num)
		{
			if($this->bonds==$num)
			{
				return;
			}
			if($num < 0)
			{
				throw new Exception('You cannot set negative bonds.');
			}
			$this->bonds = $num;
		}

		public function increaseBonds($num)
		{
			if($num == 0)
			{
				return;
			}
			$this->setBonds($this->getBonds() + $num);
		}

		public function decreaseBonds($num)
		{
			if($num == 0)
			{
				return;
			}
			$this->setBonds($this->getBonds() - $num);
		}

		public function checkForExcessDefense()
		{
			if ($this->getShields() > $this->getMaxShields())
			{
				$this->setShields($this->getMaxShields());
			}
			if ($this->getCDs() > $this->getMaxCDs())
			{
				$this->setCDs($this->getMaxCDs());
			}
		}

		public function getShields($delayed = false)
		{
			return $this->shields + ($delayed?$this->delayedShieldsDelta:0);
		}

		public function hasShields($delayed = false)
		{
			return $this->getShields($delayed)>0;
		}

		public function setShields($shields)
		{
			if($shields<0)
			{
				$shields=0;
			}
			if ($shields > $this->getMaxShields())
			{
				$shields = $this->getMaxShields();
			}
			if($this->shields == $shields)
			{
				return;
			}
			$this->shields=$shields;
		}

		public function decreaseShields($number, $delayed = false)
		{
			if($number == 0)
			{
				return;
			}
			if($delayed===false)
			{
				$this->setShields($this->getShields() - $number);
			}
			else
			{
				$this->delayedShieldsDelta -= $number;
			}
		}

		public function increaseShields($number, $delayed = false)
		{
			if($number == 0)
			{
				return;
			}
			if($delayed===false)
			{
				$this->setShields($this->getShields() + $number);
			}
			else
			{
				$this->delayedShieldsDelta += $number;
			}
		}

		public function getMaxShields()
		{
			return $this->getBuilding(PLANET_GENERATOR) * PLANET_GENERATOR_SHIELDS;
		}

		public function getCDs($delayed = false)
		{
			return $this->drones + ($delayed?$this->delayedCDsDelta:0);
		}

		public function hasCDs($delayed = false)
		{
			return $this->getCDs($delayed)>0;
		}

		public function setCDs($combatDrones)
		{
			if($combatDrones<0)
			{
				$combatDrones=0;
			}
			if ($combatDrones > $this->getMaxCDs())
			{
				$combatDrones = $this->getMaxCDs();
			}
			if($this->drones == $combatDrones)
			{
				return;
			}
			$this->drones=$combatDrones;
		}

		public function decreaseCDs($number, $delayed = false)
		{
			if($number == 0)
			{
				return;
			}
			if($delayed===false)
			{
				$this->setCDs($this->getCDs() - $number);
			}
			else
			{
				$this->delayedCDsDelta -= $number;
			}
		}

		public function increaseCDs($number, $delayed = false)
		{
			if($number == 0)
			{
				return;
			}
			if($delayed===false)
			{
				$this->setCDs($this->getCDs() + $number);
			}
			else
			{
				$this->delayedCDsDelta += $number;
			}
		}

		public function getMaxCDs()
		{
			return $this->getBuilding(PLANET_HANGAR) * PLANET_HANGAR_DRONES;
		}

		public function isDestroyed($delayed = false)
		{
			return !$this->hasCDs($delayed) && !$this->hasShields($delayed);
		}

		public function exists()
		{
			return $this->getGameID()!=null && $this->getSectorID()!==null;
		}

		public function getStockpile($goodID=false)
		{
			if(!isset($this->stockpile))
			{
				// initialize cargo array
				$this->stockpile = array();
				// get supplies from db
				$this->db->query('SELECT * FROM planet_has_cargo WHERE game_id = '.$this->game_id.' AND ' .
																'sector_id = '.$this->sector_id);
				// adding cargo and amount to array
				while ($this->db->nextRecord())
				$this->stockpile[$this->db->getField('good_id')] = $this->db->getField('amount');
			}
			if($goodID===false)
			return $this->stockpile;
			if(isset($this->stockpile[$goodID]))
			return $this->stockpile[$goodID];
			return 0;
		}

		public function hasStockpile($goodID=false)
		{
			return $this->getStockpile($goodID) > 0;
		}

		public function setStockpile($goodID,$amount)
		{
			if($amount < 0)
			throw new Exception('Trying to set negative stockpile.');
			if($this->getStockpile($goodID) == $amount)
			return;
			$this->stockpile[$goodID] = $amount;
			$this->hasChangedStockpile = true;
		}

		public function decreaseStockpile($goodID,$amount)
		{
			if($amount < 0)
			throw new Exception('Trying to decrease negative stockpile.');
			$this->setStockpile($goodID,$this->getStockpile($goodID) - $amount);
		}

		public function increaseStockpile($goodID,$amount)
		{
			if($amount < 0)
			throw new Exception('Trying to increase negative stockpile.');
			$this->setStockpile($goodID,$this->getStockpile($goodID) + $amount);
		}

		public function getBuildings()
		{
			if(!isset($this->buildings))
			{
				$this->buildings = array();
					
				// get buildingss from db
				$this->db->query('SELECT * FROM planet_has_building WHERE game_id = '.$this->getGameID().' AND ' .
																 'sector_id = '.$this->getSectorID());
				// adding building and amount to array
				while ($this->db->nextRecord())
				$this->buildings[$this->db->getField('construction_id')] = $this->db->getField('amount');
			}
			return $this->buildings;
		}

		public function getBuilding($buildingTypeID)
		{
			$buildings = $this->getBuildings();
			if(isset($buildings[$buildingTypeID]))
			return $buildings[$buildingTypeID];
			return 0;
		}

		public function hasBuilding($buildingTypeID)
		{
			return $this->getBuilding($buildingTypeID)>0;
		}

		public function setBuilding($buildingTypeID,$number)
		{
			$this->buildings[$buildingTypeID] = $number;
		}

		public function destroyBuilding($buildingTypeID,$number)
		{
			if($this->hasBuilding($buildingTypeID))
			{
				$this->setBuilding($buildingTypeID,$this->getBuilding($buildingTypeID)-$number);
			}
		}

		public function getTotalBuildings()
		{
			$totalBuildings = 0;
			foreach($this->getBuildings() as $building)
			{
				$totalBuildings += $building;
			}
			return $totalBuildings;
		}

		function getCurrentlyBuilding()
		{
			if(!isset($this->currentlyBuilding))
			{
				$toBeUpdated = false;
				$this->currentlyBuilding = array();
				$this->db->query('SELECT * FROM planet_is_building ' .
							 'WHERE game_id = '.$this->game_id.' AND ' .
								   'sector_id = '.$this->sector_id);
				while($this->db->nextRecord())
				{
					if($this->db->getField('time_complete') <= TIME)
					{
						$PLANET_BUILDINGS =& Globals::getPlanetBuildings();
						$player =& SmrPlayer::getPlayer($this->db->getField('constructor_id'),$this->getGameID());
						$player->increaseHOF(1,array('Planet','Buildings','Built'), HOF_ALLIANCE);
						$expGain = $PLANET_BUILDINGS[$this->db->getField('construction_id')]['ExpGain'];
						$player->increaseExperience($expGain);
						$player->increaseHOF(1,array('Planet','Buildings','Experience'), $expGain);
						$db2 = new SmrMySqlDatabase();
						$db2->query('DELETE FROM planet_is_building WHERE game_id = '.$this->getGameID().' AND ' .
										 'sector_id = '.$this->getSectorID() . ' AND building_slot_id = ' . $this->db->getField('building_slot_id') . ' LIMIT 1');
						if(isset($this->buildings[$this->db->getField('construction_id')]))
						{
							$this->buildings[$this->db->getField('construction_id')] += 1;
						}
						else
						{
							$this->buildings[$this->db->getField('construction_id')] = 1;
						}
						$toBeUpdated = true;
					}
					else
					{
						$this->currentlyBuilding[$this->db->getField('building_slot_id')] = array(
						'BuildingSlotID' => $this->db->getField('building_slot_id'),
						'ConstructionID' => $this->db->getField('construction_id'),
						'ConstructorID' => $this->db->getField('constructor_id'),
						'Finishes' => $this->db->getField('time_complete'),
						'TimeRemaining' => $this->db->getField('time_complete') - TIME
						);
					}
				}
				if($toBeUpdated)
				{
					$this->update();
				}
			}
			return $this->currentlyBuilding;
		}

		public function getMaxBuildings($buildingTypeID=false)
		{
			if(!isset($this->max_construction))
			{
				//			global $PRIMARY_BUILDINGS;
				//			$PLANET_BUILDINGS = Globals::getPlanetBuildings();
				$this->max_construction = array();

				// get buildingss from db
				$this->db->query('SELECT * FROM planet_construction');
				while ($this->db->nextRecord())
				{
					$construction_id	= $this->db->getField('construction_id');
					$max_construction	= $this->db->getField('max_construction');

					$this->max_construction[$construction_id] = $max_construction;
				}
			}
			if($buildingTypeID===false)
			{
				return $this->max_construction;
			}
			return $this->max_construction[$buildingTypeID];
			//				if (isset($PRIMARY_BUILDINGS[$building_type_id]))
			//					$planet['Defined'] = TRUE;
			//
			//					if (isset($PLANET_BUILDINGS[$building_type_id]['Unlocks']))
			//					{
			//						foreach ($PLANET_BUILDINGS[$building_type_id]['Unlocks'] as $building_id => $max_amount)
			//							$planet['Building Types Available'][$building_id] += $max_amount;
			//					}
			//
			//					//define planet
			//					if (isset($PRIMARY_BUILDINGS[$building_type_id]))
			//					{
			//						$planet['Type ID'] = $PRIMARY_BUILDINGS[$building_type_id];
			//						query('UPDATE planet SET planet_type = ' . $planet['Type ID'] . ' WHERE '.$planet['SQL'].' LIMIT 1');
			//					}
	}

	public function doDelayedUpdates()
	{
		$this->setShields($this->getShields(true));
		$this->delayedShieldsDelta = 0;
		$this->setCDs($this->getCDs(true));
		$this->delayedCDsDelta = 0;
	}

	public function update()
	{
		$this->doDelayedUpdates();
		$this->db->query('UPDATE planet SET owner_id = '.$this->owner_id.', ' .
									'password = '.$this->db->escapeString($this->password).', ' .
									'planet_name = ' . $this->db->escapeString($this->planet_name) . ', ' .
									'shields = '.$this->shields.', ' .
									'drones = '.$this->drones.', ' .
									'credits = '.$this->credits.', ' .
									'bonds = '.$this->bonds.', ' .
									'maturity = '.$this->maturity.' ' .
								'WHERE game_id = '.$this->game_id.' AND ' .
									  'sector_id = '.$this->sector_id);

		if($this->hasChangedStockpile)
		{
			// write stockpile info
			foreach ($this->getStockpile() as $id => $amount)
			{
				if ($amount != 0)
				{
					$this->db->query('REPLACE INTO planet_has_cargo (game_id, sector_id, good_id, amount) ' .
										 'VALUES('.$this->getGameID().', '.$this->getSectorID().', '.$id.', '.$amount.')');
				}
				else
				{
					$this->db->query('DELETE FROM planet_has_cargo WHERE game_id = '.$this->getGameID().' AND ' .
										'sector_id = '.$this->getSectorID().' AND ' .
										'good_id = '.$id);
				}
			}
		}

		// write building info
		foreach ($this->getBuildings() as $id => $amount)
		{
			if ($amount != 0)
			{
				$this->db->query('REPLACE INTO planet_has_building (game_id, sector_id, construction_id, amount) ' .
									'VALUES('.$this->game_id.', '.$this->sector_id.', '.$id.', '.$amount.')');
			}
			else
			{
				$this->db->query('DELETE FROM planet_has_building WHERE game_id = '.$this->game_id.' AND ' .
								   'sector_id = '.$this->sector_id.' AND ' .
								   'construction_id = '.$id);
			}
		}
	}

	function getLevel()
	{
		return ($this->getBuilding(1) + $this->getBuilding(2) + $this->getBuilding(3)) / 3;
	}

	function accuracy()
	{
		if($this->hasWeapons())
		{
			$weapons =& $this->getWeapons();
			return $weapons[0]->getModifiedPlanetAccuracy($this);
		}
		return 0;
	}

	function stockpile_left($id)
	{
		return 600 - $this->getStockpile($id);
	}

	/**
	 * Returns true if there is a building in progress
	 */
	function hasCurrentlyBuilding()
	{
		return count($this->getCurrentlyBuilding()) > 0;
	}

	function startBuilding($constructor_id,$construction_id)
	{
		$PLANET_BUILDINGS =& Globals::getPlanetBuildings();
		// take the goods that are needed
		foreach($PLANET_BUILDINGS[$construction_id]['Goods'] as $goodID => $amount)
		{
			$this->decreaseStockpile($goodID,$amount);
		}

		$this->update();


		// gets the time for the buildings
		$time_complete = TIME + round($PLANET_BUILDINGS[$construction_id]['Build Time'] / Globals::getGameSpeed($this->getGameID()));

		$this->db->query('INSERT INTO planet_is_building (game_id, sector_id, construction_id, constructor_id, time_complete) ' .
												'VALUES ('.$this->getGameID().', '.$this->getSectorID().', '.$construction_id.', '.$constructor_id.','.$time_complete.')');

		$this->currentlyBuilding[$this->db->getInsertID()] = array(
			'BuildingSlotID' => $this->db->getInsertID(),
			'ConstructionID' => $construction_id,
			'ConstructorID' => $constructor_id,
			'Finishes' => $time_complete,
			'TimeRemaining' => $time_complete - TIME
		);
	}

	function stopBuilding($constructionID)
	{
		$currentlyBuilding = $this->getCurrentlyBuilding();
		$matchingBuilding = false;
		$latestFinish = 0;
		foreach($currentlyBuilding as $key => $building)
		{
			if($building['ConstructionID'] == $constructionID && $building['Finishes'] > $latestFinish)
			{
				$latestFinish = $building['Finishes'];
				$matchingBuilding = $building;
			}
		}
		if($matchingBuilding)
		{
			$this->db->query('DELETE FROM planet_is_building WHERE sector_id = '.$this->getSectorID().' AND game_id = '.$this->getGameID().' AND building_slot_id = ' . $matchingBuilding['BuildingSlotID'].' LIMIT 1');
			unset($this->currentlyBuilding[$matchingBuilding['BuildingSlotID']]);
			return true;
		}
		return false;
	}

	public function getName()
	{
		return $this->planet_name;
	}

	public function setName($name)
	{
		$this->planet_name = $name;
	}

	public function getDisplayName()
	{
		return '<span style="color:yellow;font-variant:small-caps">' . $this->getName() . '(#'.$this->getSectorID().')</span>';;
	}

	public function isInhabitable()
	{
		return $this->inhabitable_time<= TIME;
	}

	public function isClaimed()
	{
		return $this->owner_id>0;
	}

	public function removeClaimed()
	{
		$this->setOwnerID(0);
	}

	public function getExamineHREF()
	{
		return SmrSession::get_new_href(create_container('skeleton.php', 'planet_examine.php'));
	}

	public function getLandHREF()
	{
		return SmrSession::get_new_href(create_container('planet_land_processing.php'));
	}

	public function getAttackHREF()
	{
		return SmrSession::get_new_href(create_container('planet_attack_processing.php'));
	}


	public function attackedBy(AbstractSmrPlayer $trigger, array $attackers)
	{
		$trigger->increaseHOF(1,array('Combat','Planet','Number Of Triggers'), HOF_PUBLIC);
		foreach ($attackers as $attacker)
		{
			$attacker->increaseHOF(1,array('Combat','Planet','Number Of Attacks'), HOF_PUBLIC);
			$this->db->query('REPLACE INTO player_attacks_planet (game_id, account_id, sector_id, time, level) VALUES ' .
					'('.$this->getGameID().', '.$attacker->getAccountID().', '.$this->getSectorID().', ' . TIME . ', ' . $this->getLevel() . ')');
		}

		$allowed = TIME - 600;
		$this->db->getField('SELECT * FROM news WHERE type = \'BREAKING\' AND game_id = '.$trigger->getGameID().' AND time > '.$allowed);
		if ($this->db->getNumRows()==0)
		{
			if (count($attackers) >= 5)
			{
				$owner =& $this->getOwner();
				$text = count($attackers) . ' members of '.$trigger->getAllianceBBLink().' have been spotted attacking ' .
				$this->getDisplayName() . ' in sector #' . $this->getSectorID() . '.  The planet is owned by ' . $owner->getBBLink();
				if ($owner->hasAlliance())
				{
					$text .= ', a member of ' . $owner->getAllianceBBLink();
				}
				$text .= '.';
				$this->db->query('INSERT INTO news (game_id, time, news_message, type,killer_id,killer_alliance,dead_id,dead_alliance) VALUES ('.$this->getGameID().', ' . TIME . ', '.$this->db->escapeString($text).', \'BREAKING\','.$trigger->getAccountID().','.$trigger->getAllianceID().','.$owner->getAccountID().','.$owner->getAllianceID().')');
			}
		}
	}

	public function &getWeapons()
	{
		$weapons = array();
		for($i=0;$i<$this->getBuilding(PLANET_TURRET);++$i)
		{
			$weapons[$i] =& SmrWeapon::getWeapon(Globals::getGameType($this->getGameID()),WEAPON_PLANET_TURRET);
		}
		return $weapons;
	}

	public function hasWeapons()
	{
		return count($this->getWeapons())>0;
	}

	public function &shootPlayer(AbstractSmrPlayer &$targetPlayer)
	{
		return $this->shootPlayers(array($targetPlayer));
	}

	public function &shootPlayers(array &$targetPlayers)
	{
		$results = array('Planet' => &$this, 'TotalDamage' => 0, $results['TotalDamagePerTargetPlayer'] = array());
		foreach($targetPlayers as &$targetPlayer)
		{
			$results['TotalDamagePerTargetPlayer'][$targetPlayer->getAccountID()] = 0;
		} unset($targetPlayer);
		if($this->isDestroyed())
		{
			$results['DeadBeforeShot'] = true;
			return $results;
		}
		$results['DeadBeforeShot'] = false;
		$weapons =& $this->getWeapons();
		foreach($weapons as $orderID => &$weapon)
		{
			$results['Weapons'][$orderID] =& $weapon->shootPlayerAsPlanet($this, $targetPlayers[array_rand($targetPlayers)]);
			if($results['Weapons'][$orderID]['Hit'])
			{
				$results['TotalDamage'] += $results['Weapons'][$orderID]['ActualDamage']['TotalDamage'];
				$results['TotalDamagePerTargetPlayer'][$results['Weapons'][$orderID]['TargetPlayer']->getAccountID()] += $results['Weapons'][$orderID]['ActualDamage']['TotalDamage'];
			}
		}
		if($this->hasCDs())
		{
			$thisCDs = new SmrCombatDrones($this->getGameID(),$this->getCDs(),true);
			$results['Drones'] =& $thisCDs->shootPlayerAsPlanet($this, $targetPlayers[array_rand($targetPlayers)]);
			$results['TotalDamage'] += $results['Drones']['ActualDamage']['TotalDamage'];
			$results['TotalDamagePerTargetPlayer'][$results['Drones']['TargetPlayer']->getAccountID()] += $results['Drones']['ActualDamage']['TotalDamage'];
		}
		return $results;
	}

	function &checkForDowngrade($damage)
	{
		$results = '';
		// Chance of planetary structure damage = For every 70 damage there is a 15% chance of destroying a structure.
		// Turrets have a 1 in 6 chance of being destroyed
		// Hangers have a 2 in 6 chance of being destroyed
		// Generators 3 in 6 chance of being destroyed
		//iterate over all chances
		for ($i = 0; $damage > self::DAMAGE_NEEDED_FOR_DOWNGRADE_CHANCE; $damage-=self::DAMAGE_NEEDED_FOR_DOWNGRADE_CHANCE)
		{
			//15% chance to destroy something
			if (mt_rand(1, 100) <= self::CHANCE_TO_DOWNGRADE)
			{
				$rand = mt_rand(1, 6);
				switch ($rand) {
					case 1:
						//destroy a turret
						if ($this->hasBuilding(PLANET_TURRET))
						{
							$results .= 'This team destroys <span style = "color:red;">1</span> turret.<br />';
							$this->destroyBuilding(PLANET_TURRET, 1);
							break;
						}
						//if no turrets we fall through
					case 2:
					case 3:
						//destroy a hangar
						if ($this->hasBuilding(PLANET_HANGAR))
						{
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
						if ($this->hasBuilding(PLANET_GENERATOR))
						{
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

	public function &doWeaponDamage(array $damage, $delayed)
	{
		$alreadyDead = $this->isDestroyed(true);
		$shieldDamage = 0;
		$cdDamage = 0;
		$armourDamage = 0;
		if(!$alreadyDead)
		{
			if($damage['Shield'] || !$this->hasShields(true))
			{
				$shieldDamage = $this->doShieldDamage(min($damage['MaxDamage'],$damage['Shield']),$delayed);
				$damage['MaxDamage'] -= $shieldDamage;
				if(!$this->hasShields(true) && ($shieldDamage == 0 || $damage['Rollover']))
				{
					$cdDamage = $this->doCDDamage(min($damage['MaxDamage'],$damage['Armour']),$delayed);
					$damage['Armour'] -= $cdDamage;
					$damage['MaxDamage'] -= $cdDamage;
				}
			}
			else //hit drones behind shields
			{
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

	protected function doShieldDamage($damage, $delayed)
	{
		$actualDamage = min($this->getShields(true),$damage);
		$this->decreaseShields($actualDamage, $delayed);
		return $actualDamage;
	}

	protected function doCDDamage($damage, $delayed)
	{
		$actualDamage = min($this->getCDs(true),floor($damage/CD_ARMOUR));
		$this->decreaseCDs($actualDamage, $delayed);
		return $actualDamage*CD_ARMOUR;
	}

	public function creditCurrentAttackersForKill()
	{
		//get all players involved for HoF
		$allowed = TIME - 60 * 60 * 3;
		$this->db->query('SELECT account_id,level FROM player_attacks_planet WHERE game_id = '.$this->getGameID().' AND sector_id = '.$this->getSectorID().' AND time > '.$allowed);
		$temp = array();
		while ($this->db->nextRecord())
		{
			$currPlayer =& SmrPlayer::getPlayer($this->db->getField('account_id'),$this->getGameID());
			$currPlayer->increaseHOF($this->db->getField('level'),array('Combat','Planet','Levels'), HOF_PUBLIC);
			$currPlayer->increaseHOF(1,array('Combat','Planet','Completed'), HOF_PUBLIC);
		}
		$this->db->query('DELETE FROM player_attacks_planet WHERE sector_id = '.$this->getSectorID().' AND game_id = '.$this->getGameID());
	}

	public function &killPlanetByPlayer(AbstractSmrPlayer &$killer)
	{
		$return = array();
		$this->creditCurrentAttackersForKill();

		//kick everyone from planet
		$this->db->query('UPDATE player SET land_on_planet = \'FALSE\' WHERE sector_id = '.$this->getSectorID().' AND game_id = '.$this->getGameID());
		$this->removeClaimed();
		$this->removePassword();
		return $return;
	}
}

?>