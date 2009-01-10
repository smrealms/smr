<?
require_once('SmrMySqlDatabase.class.inc');

class SmrPlanet
{
	protected static $CACHE_PLANETS = array();
	
	protected $db;
	
	var $sector_id;
	var $game_id;
	var $planet_name;
	var $owner_id;
	var $password;
	var $shields;
	var $drones;
	var $credits;
	var $bonds;
	var $maturity;
	protected $stockpile;
	protected $buildings;
	var $max_construction;
	var $inhabitable_time;
	var $level;
	
	protected $hasChangedStockpile = false;
	
	protected $examineHREF;

	public static function &getPlanet($gameID,$sectorID,$forceUpdate = false)
	{
		if($forceUpdate || !isset(self::$CACHE_PLANETS[$gameID . ':' . $sectorID]))
		{
			$p = new SmrPlanet($gameID,$sectorID);
			return self::$CACHE_PLANETS[$gameID . ':' . $sectorID] =& $p;
		}
		return self::$CACHE_PLANETS[$gameID . ':' . $sectorID];
	}

	protected function __construct($gameID,$sectorID)
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
			$this->level = $this->level();
		}
	}
	
	public function getGameID()
	{
		return $this->game_id;
	}
	
	public function getSectorID()
	{
		return $this->sector_id;
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
			$this->currentlyBuilding = array();
			$this->db->query('SELECT * FROM planet_is_building ' .
								 'WHERE game_id = '.$this->game_id.' AND ' .
									   'sector_id = '.$this->sector_id);
			while($this->db->nextRecord())
			{
				if($this->db->getField('time_complete') <= TIME)
				{
					$this->db->query('DELETE FROM planet_is_building WHERE game_id = '.$this->getGameID().' AND ' .
											 'sector_id = '.$this->getSectorID() . ' AND building_slot_id = ' . $this->db->getField('building_slot_id') . ' LIMIT 1');
					$this->getBuilding[$this->db->getField('construction_id')] += 1;
					$this->update();
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
		}
	}
		
	public function getMaxBuildings()
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
		return $this->max_construction;
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

	function update() {

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
					$this->db->query('REPLACE INTO planet_has_cargo (game_id, sector_id, good_id, amount) ' .
														 'VALUES('.$this->getGameID().', '.$this->getSectorID().', '.$id.', '.$amount.')');
				else
					$this->db->query('DELETE FROM planet_has_cargo WHERE game_id = '.$this->getGameID().' AND ' .
														'sector_id = '.$this->getSectorID().' AND ' .
														'good_id = '.$id);
			}
		}

		// write building info
		foreach ($this->getBuildings() as $id => $amount) {

			if ($amount != 0)
				$this->db->query('REPLACE INTO planet_has_building (game_id, sector_id, construction_id, amount) ' .
															'VALUES('.$this->game_id.', '.$this->sector_id.', '.$id.', '.$amount.')');
			else
				$this->db->query('DELETE FROM planet_has_building WHERE game_id = '.$this->game_id.' AND ' .
																	   'sector_id = '.$this->sector_id.' AND ' .
																	   'construction_id = '.$id);
		}
	}

	function level()
	{
		return number_format(($this->getBuilding(1) +
							  $this->getBuilding(2) +
							  $this->getBuilding(3)) / 3, 2);
	}

	function accuracy()
	{
		return round(25 + $this->level());
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
		// take the goods that are needed
		$this->db->query('SELECT * FROM planet_cost_good, good ' .
							  'WHERE planet_cost_good.good_id = good.good_id AND ' .
									'construction_id = '.$construction_id.' ' .
							  'ORDER BY good.good_id');
		while ($this->db->nextRecord())
		{
			$good_id	= $this->db->getField('good_id');
			$amount		= $this->db->getField('amount');

			$this->decreaseStockpile($good_id,$amount);
		}

		$this->update();

		// get game speed
		$this->db->query('SELECT * FROM game WHERE game_id = '.$this->game_id);
		if ($this->db->nextRecord())
			$game_speed = $this->db->getField('game_speed');

		// gets the time for the buildings
		$this->db->query('SELECT * FROM planet_cost_time WHERE construction_id = '.$construction_id);
		if ($this->db->nextRecord())
			$time_complete = time() + round($this->db->getField('amount') / $game_speed);

		$this->db->query('INSERT INTO planet_is_building (game_id, sector_id, construction_id, constructor_id, time_complete) ' .
													'VALUES ('.$this->game_id.', '.$this->sector_id.', '.$construction_id.', '.$constructor_id.','.$time_complete.')');
		
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
			unset($this->currentlyBuilding[$matchingBuilding['BuildingSlotID']]);
			$db->query('DELETE FROM planet_is_building WHERE sector_id = '.$player->getSectorID().' AND game_id = '.$player->getGameID().' AND building_slot_id = ' . $matchingBuilding['BuildingSlotID'].' LIMIT 1');
			return true;
		}
		return false;
	}
	
	public function getName()
	{
		return $this->planet_name;
	}
	
	public function isInhabitable()
	{
		return $this->inhabitable_time<= TIME;
	}
	
	public function isClaimed()
	{
		return $this->owner_id>0;
	}
	
	public function getExamineHREF()
	{
		if(!isset($this->examineHREF))
		{
			$container = array();
			$container['url'] = 'skeleton.php';
			$container['body'] = 'planet_examine.php';
			$this->examineHREF = SmrSession::get_new_href($container);
		}
		return $this->examineHREF;
	}
}

?>