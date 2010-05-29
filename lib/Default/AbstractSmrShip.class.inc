<?php
require_once('AbstractSmrPlayer.class.inc');
require_once('SmrWeapon.class.inc');
require_once('SmrCombatDrones.class.inc');
require_once('SmrForce.class.inc');
abstract class AbstractSmrShip
{
	protected static $CACHE_BASE_SHIPS = array();
	
	protected $player;
	
	protected $gameID;
	protected $baseShip;
	
	protected $hardware;
	protected $oldHardware;
	
	protected $cargo;
	
	protected $weapons;
	
	protected $illusionShip;
	
	protected $hasChangedWeapons = false;
	protected $hasChangedCargo = false;
	protected $hasChangedHardware = false;

	public static function &getBaseShip($gameTypeID,$shipTypeID,$forceUpdate = false)
	{
		if($forceUpdate || !isset(self::$CACHE_BASE_SHIPS[$gameTypeID][$shipTypeID]))
		{
			// determine ship
			$db = new SmrMySqlDatabase();
			$db->query('SELECT * FROM ship_type WHERE ship_type_id = '.$shipTypeID.' LIMIT 1'); //TODO add game type id
			if($db->nextRecord())
				self::$CACHE_BASE_SHIPS[$gameTypeID][$shipTypeID] =& self::buildBaseShip($db);
			else
				self::$CACHE_BASE_SHIPS[$gameTypeID][$shipTypeID] = false;
		}
		return self::$CACHE_BASE_SHIPS[$gameTypeID][$shipTypeID];
	}
	
	protected static function &buildBaseShip(Database &$db)
	{
		$ship = array();
		$ship['Type']				= 'Ship';
		$ship['Name']				= $db->getField('ship_name');
		$ship['ShipTypeID']			= $db->getField('ship_type_id');
		$ship['RaceID']				= $db->getField('race_id');
		$ship['Hardpoint']			= $db->getField('hardpoint');
		$ship['Speed']				= $db->getField('speed');
		$ship['Cost']				= $db->getField('cost');
		$ship['AlignRestriction']	= $db->getField('buyer_restriction');
		$ship['Level']				= $db->getField('lvl_needed');
		
		$maxPower = 0;
		switch($ship['Hardpoint'])
		{
			default:
				$maxPower+=1*$ship['Hardpoint']-10;
			case 10:
				$maxPower+=2;
			case 9:
				$maxPower+=2;
			case 8:
				$maxPower+=2;
			case 7:
				$maxPower+=2;
			case 6:
				$maxPower+=3;
			case 5:
				$maxPower+=3;
			case 4:
				$maxPower+=3;
			case 3:
				$maxPower+=4;
			case 2:
				$maxPower+=4;
			case 1:
				$maxPower+=5;
			case 0:
				$maxPower+=0;
		}
		$ship['MaxPower'] = $maxPower;
		
		
		// get supported hardware from db
		$db2 = new SmrMySqlDatabase();
		$db2->query('SELECT hardware_type_id, max_amount FROM ship_type_support_hardware ' .
			'WHERE ship_type_id = '.$ship['ShipTypeID'].' ORDER BY hardware_type_id');
		
		while ($db2->nextRecord())
		{
			// adding hardware to array
			$ship['MaxHardware'][$db2->getField('hardware_type_id')] = $db2->getField('max_amount');
		}
		
		$ship['BaseMR'] = 	round(
								700 - 
								(
									(
										$ship['MaxHardware'][HARDWARE_SHIELDS]
										+$ship['MaxHardware'][HARDWARE_ARMOUR]
										+$ship['MaxHardware'][HARDWARE_COMBAT]*3
									)/25
									+(
										$ship['MaxHardware'][HARDWARE_CARGO]/100
										-$ship['Speed']*5
										+$ship['Hardpoint']*5
										+$ship['MaxHardware'][HARDWARE_COMBAT]/5
									)
								)
							);
		return $ship;
	}

	public static function &getAllBaseShips($gameTypeID,$forceUpdate = false)
	{
		// determine ship
		$db = new SmrMySqlDatabase();
		$db->query('SELECT * FROM ship_type ORDER BY ship_type_id ASC'); //TODO add game type id
		while($db->nextRecord())
			if(!isset(self::$CACHE_BASE_SHIPS[$gameTypeID][$db->getField('ship_type_id')]))
				self::$CACHE_BASE_SHIPS[$gameTypeID][$db->getField('ship_type_id')] =& self::buildBaseShip($db);
		return self::$CACHE_BASE_SHIPS[$gameTypeID];
	}
	
	public static function compareName($a,$b)
	{
		$aName = $a['Name'];
		$bName = $b['Name'];
	    if ($aName == $bName)
	        return 0;
	    return $aName < $bName ? -1 : 1;
	}
	
	protected function __construct(AbstractSmrPlayer &$player)
	{
		$this->player = $player;
		$this->gameID = $player->getGameID();
		$this->regenerateBaseShip();
	}
	
	protected function regenerateBaseShip()
	{
		$this->baseShip =& AbstractSmrShip::getBaseShip(Globals::getGameType($this->gameID),$this->player->getShipTypeID());
		$this->checkForExcess();
	}
	
	public function checkForExcess()
	{
		$this->checkForExcessHardware();
		$this->checkForExcessMiningDrones();
		$this->checkForExcessWeapons();
		$this->checkForExcessCargo();
	}
	
	public function checkForExcessWeapons()
	{
		while ($this->hasWeapons() && ($this->getPowerUsed() > $this->getMaxPower() || $this->getNumWeapons() > $this->getHardpoints()))
		{
			//erase the first weapon 1 at a time until we are okay
			$this->removeLastWeapon();
		}
	}
	
	public function checkForExcessCargo()
	{
		if($this->hasCargo())
		{
			while(($total=array_sum($goods=$this->getCargo())) > $this->getCargoHolds())
			{
				do
				{
					list($goodID,$amount)=each($goods);
				} while($amount==0);
				if($amount > $total-$this->getCargoHolds())
					$amount = $total-$this->getCargoHolds();
				$this->decreaseCargo($goodID,$amount);
			}
		}
	}
	
	public function checkForExcessMiningDrones()
	{
		if (($totalDrones = array_sum($drones = $this->getMiningDrones())) > $this->getMaxMiningDrones())
		{
			$need_to_lose = $totalDrones - $this->getMaxMiningDrones();
			foreach ($drones as $type => $amount)
			{
				if ($amount > $need_to_lose)
					$remove[$type] = $need_to_lose;
				else
					$remove[$type] = $amount;
				$need_to_lose -= $remove[$type];
			}
			foreach ($remove as $type => $amount)
			{
				$this->decreaseMiningDrones($type,$amount);
			}
		}
	}
	
	public function checkForExcessHardware()
	{
		//check hardware to see if anything needs to be removed
		if(is_array($hardware = $this->getHardware()))
			foreach ($hardware as $hardwareTypeID => $amount)
			{
				if (hardware_is_mining_drone($hardwareTypeID)) continue;
				if ($amount > ($max=$this->getMaxHardware($hardwareTypeID)))
				{
					$this->setHardware($hardwareTypeID,$max,true);
				}
			}
	}
	
	public function getPowerUsed()
	{
		$power = 0;
		if($this->getNumWeapons()>0)
			foreach ($this->weapons as &$weapon)
				$power += $weapon->getPowerLevel();
		return $power;
	}
	
	public function getRemainingPower()
	{
		return $this->getMaxPower()-$this->getPowerUsed();
	}
	
	public function hasRemainingPower()
	{
		return $this->getRemainingPower()>0;
	}
	
	public function getMaxPower()
	{
		return $this->baseShip['MaxPower'];
	}
	
	public function hasIllegalGoods()
	{
		return $this->hasCargo(5) || $this->hasCargo(9) || $this->hasCargo(12);
	}
	
	public function getDisplayAttackRating(AbstractSmrPlayer &$player)
	{
		if($this->hasActiveIllusion())
			return $this->getIllusionAttack();
		else
			return $this->getAttackRating();
	}
	
	public function getDisplayDefenseRating()
	{
		if($this->hasActiveIllusion())
			return $this->getIllusionDefense();
		else
			return $this->getDefenseRating();
	}
	
	public function getAttackRating()
	{
		return round(($this->getTotalShieldDamage() + $this->getTotalArmourDamage() + $this->getCDs() * .7) / 40);
	}
	
	public function getDefenseRating()
	{
		return round((($this->getShields() + $this->getArmour()) / 100) + (($this->getCDs() * 3) / 100));
	}
	
	public function shield_low() { return floor($this->getShields() / 100) * 100; }
	public function shield_high() { return $this->shield_low() + 100; }
	public function armour_low() { return floor($this->getArmour() / 100) * 100; }
	public function armour_high() { return $this->armour_low() + 100; }
	public function combat_drones_low() { return floor($this->getCDs() / 100) * 100; }
	public function combat_drones_high() { return $this->combat_drones_low() + 100; }
	
	
	
	public function &addWeapon($weaponTypeID)
	{
		if($this->hasOpenWeaponSlots() && $this->hasRemainingPower())
		{
			$weapon =& SmrWeapon::getWeapon($this->getGameID(),$weaponTypeID);
			if($this->getRemainingPower()>=$weapon->getPowerLevel())
			{
				$this->weapons[count($this->weapons)] =& $weapon;
				$this->hasChangedWeapons = true;
				return $weapon;
			}
		}
		$return = false;
		return $return;
	}
	
	public function moveWeaponUp($orderID)
	{
		$replacement = $orderID - 1;
		if($replacement < 0)
		{
			// Shift everything up by one and put the selected weapon at the bottom
			$temp =& $this->weapons[$orderID];
			for($i=1;$i<count($this->weapons);++$i)
			{
				$this->weapons[$i-1] =& $this->weapons[$i]; //If we go above the bounds of the array it will keep on going due to assign by ref.  Hence $i-1 =& $i not $i =& $i+1
			}
			$this->weapons[count($this->weapons)-1] =& $temp;
		}
		else
		{
			$temp =& $this->weapons[$replacement];
			$this->weapons[$replacement] =& $this->weapons[$orderID];
			$this->weapons[$orderID] =& $temp;
		}
		$this->hasChangedWeapons = true;
	}
	
	public function moveWeaponDown($orderID)
	{
		$replacement = $orderID + 1;
		if($replacement >= count($this->weapons))
		{
			// Shift everything down by one and put the selected weapon at the top
			$temp =& $this->weapons[count($this->weapons)-1];
			for($i=count($this->weapons)-1;$i>0;--$i)
			{
				$this->weapons[$i] =& $this->weapons[$i-1];
			}	
			$this->weapons[0] =& $temp;
		}
		else
		{
			$temp =& $this->weapons[$replacement];
			$this->weapons[$replacement] =& $this->weapons[$orderID];
			$this->weapons[$orderID] =& $temp;
		}
		$this->hasChangedWeapons = true;
	}
	
	public function setWeaponLocations(array $orderArray)
	{
		$weapons=$this->weapons;
		foreach($orderArray as $newOrder => $oldOrder)
		{
			$this->weapons[$newOrder] =& $weapons[$oldOrder];
		}
		$this->hasChangedWeapons = true;
	}
	
	public function removeLastWeapon()
	{
		$this->removeWeapon($this->getNumWeapons()-1);
	}
	
	public function removeWeapon($orderID)
	{
		// Shift everything after the removed weapon up by one and put the selected weapon at the bottom
		for($i=$orderID+1;$i<count($this->weapons);++$i)
		{
			$this->weapons[$i-1] =& $this->weapons[$i]; //If we go above the bounds of the array it will keep on going due to assign by ref.  Hence $i-1 =& $i not $i =& $i+1
		} unset($this->weapons[count($this->weapons)-1]);
		$this->hasChangedWeapons = true;
	}
	
	public function removeAllWeapons()
	{
		$this->weapons = array();
		$this->hasChangedWeapons = true;
	}
	
	public function removeAllCargo()
	{
		foreach($this->cargo as $goodID => $amount)
			$this->setCargo($goodID,0);
	}
	
	public function removeAllHardware()
	{
		foreach($this->hardware as &$hardware)
		{
			$hardware = 0;
		}
		$this->decloak();
		$this->disableIllusion();
		$this->hasChangedHardware = true;
	}
	
	public function get_pod($isNewbie = false)
	{
		$this->removeAllWeapons();
		$this->removeAllCargo();
		$this->removeAllHardware();
		
		if($isNewbie)
		{
			$this->setShields(75,true);
			$this->setArmour(150,true);
			$this->setCargoHolds(40);
			$this->setShipTypeID(SHIP_TYPE_NEWBIE_MERCHANT_VESSEL);
		}
		else
		{
			$this->setShields(50,true);
			$this->setArmour(50,true);
			$this->setCargoHolds(5);
			$this->setShipTypeID(SHIP_TYPE_ESCAPE_POD);
		}
		
		$this->removeUnderAttack();
	}
	
	public function hasCloak()
	{
		return $this->getHardware(HARDWARE_CLOAK);
	}
	
	public function canHaveCloak()
	{
		return $this->getMaxHardware(HARDWARE_CLOAK);
	}
	
	
	public function hasActiveIllusion()
	{
		if(!$this->hasIllusion())
			return false;
		return $this->getIllusionShip()!==false;
		
	}
	
	public function hasIllusion()
	{
		return $this->getHardware(HARDWARE_ILLUSION);
	}
	
	public function canHaveIllusion()
	{
		return $this->getMaxHardware(HARDWARE_ILLUSION);
	}
	
	public function hasJump()
	{
		return $this->getHardware(HARDWARE_JUMP);
	}
	
	public function canHaveJump()
	{
		return $this->getMaxHardware(HARDWARE_JUMP);
	}
	
	public function hasDCS()
	{
		return $this->getHardware(HARDWARE_DCS);
	}
	
	public function canHaveDCS()
	{
		return $this->getMaxHardware(HARDWARE_DCS);
	}
	
	public function hasScanner()
	{
		return $this->getHardware(HARDWARE_SCANNER);
	}
	
	public function canHaveScanner()
	{
		return $this->getMaxHardware(HARDWARE_SCANNER);
	}
	
	abstract public function decloak();
	
	abstract public function enableCloak();
	
	abstract public function setIllusion($ship_id, $attack, $defense);
	
	abstract public function disableIllusion();
	
	public function getIllusionShipID()
	{
		$this->getIllusionShip();
		return $this->illusionShip['ID'];
	}
	
	public function getIllusionShipName()
	{
		$this->getIllusionShip();
		return $this->illusionShip['Name'];
	}
	
	abstract public function getIllusionShip();
	
	public function getIllusionAttack()
	{
		$this->getIllusionShip();
		return $this->illusionShip['Attack'];
	}
	
	public function getIllusionDefense()
	{
		$this->getIllusionShip();
		return $this->illusionShip['Defense'];
	}
	
	public function &getPlayer()
	{
		return $this->player;
	}
	
	public function getGameID()
	{
		return $this->gameID;
	}
	
	public function getShipTypeID()
	{
		return $this->baseShip['ShipTypeID'];
	}
	
	public function setShipTypeID($shipTypeID)
	{
		$this->getPlayer()->setShipTypeID($shipTypeID);
		$this->regenerateBaseShip();
	}
	
	public function getName()
	{
		return $this->baseShip['Name'];
	}
	
	public function getCost()
	{
		return $this->baseShip['Cost'];
	}
	
	public function getCostToUpgrade($upgradeShipID)
	{
		$upgadeBaseShip =& AbstractSmrShip::getBaseShip(Globals::getGameType($this->getGameID()),$upgradeShipID);
		return $upgadeBaseShip['Cost'] - $this->getCost()/2;
	}
	
	public function getCostToUpgradeAndUNO($upgradeShipID)
	{
		$upgadeBaseShip =& AbstractSmrShip::getBaseShip(Globals::getGameType($this->getGameID()),$upgradeShipID);
		return $this->getCostToUpgrade($upgradeShipID) + $this->getCostToUNOAgainstShip($upgradeShipID);
	}
	
	protected function getCostToUNOAgainstShip($shipID)
	{
		$baseShip =& AbstractSmrShip::getBaseShip(Globals::getGameType($this->getGameID()),$shipID);
		$cost = 0;
		$hardwareTypes = array(HARDWARE_SHIELDS, HARDWARE_ARMOUR, HARDWARE_CARGO);
		foreach($hardwareTypes as $hardwareTypeID)
		{
			$cost += max(0,$baseShip['MaxHardware'][$hardwareTypeID]-$this->getHardware($hardwareTypeID))*Globals::getHardwareCost($hardwareTypeID);
		}
		return $cost;
	}
	
	public function getCostToUNO()
	{
		return $this->getCostToUNOAgainstShip($this->getShipTypeID());
	}
	
	public function getSpeed()
	{
		return $this->baseShip['Speed'];
	}
	
	public function getRealSpeed()
	{
		return $this->baseShip['Speed'] * Globals::getGameSpeed($this->getGameID());
	}
	
	public function getHardware($hardwareTypeID = false)
	{
		if($hardwareTypeID === false)
			return $this->hardware;
		return isset($this->hardware[$hardwareTypeID]) ? $this->hardware[$hardwareTypeID] : 0;
	}
	
	public function setHardware($hardwareTypeID,$amount)
	{
		if($this->getHardware($hardwareTypeID) == $amount)
			return;
		$this->hardware[$hardwareTypeID] = $amount;
		$this->hasChangedHardware = true;
	}
	
	public function increaseHardware($hardwareTypeID,$amount)
	{
		$this->setHardware($hardwareTypeID,$this->getHardware($hardwareTypeID)+$amount);
	}
	
	public function getOldHardware($hardwareTypeID = false)
	{
		if($hardwareTypeID === false)
			return $this->oldHardware;
		return isset($this->oldHardware[$hardwareTypeID]) ? $this->oldHardware[$hardwareTypeID] : 0;
	}
	
	public function setOldHardware($hardwareTypeID,$amount)
	{
		if($this->getOldHardware($hardwareTypeID) == $amount)
			return;
		$this->oldHardware[$hardwareTypeID] = $amount;
		$this->hasChangedHardware = true;
	}
	
	public function hasMaxHardware($hardwareTypeID)
	{
		return $this->getHardware($hardwareTypeID)==$this->getMaxHardware($hardwareTypeID);
	}
	
	public function getMaxHardware($hardwareTypeID = false)
	{
		if($hardwareTypeID === false)
			return $this->baseShip['MaxHardware'];
		return $this->baseShip['MaxHardware'][$hardwareTypeID];
	}
	
	public function getShields()
	{
		return $this->getHardware(HARDWARE_SHIELDS);
	}
	
	public function setShields($amount,$updateOldAmount=false)
	{
		if($updateOldAmount && !$this->hasLostShields())
			$this->setOldHardware(HARDWARE_SHIELDS,$amount);
		$this->setHardware(HARDWARE_SHIELDS,$amount);
	}
	
	public function decreaseShields($amount)
	{
		$this->setShields($this->getShields()-$amount);
	}
	
	public function increaseShields($amount)
	{
		$this->setShields($this->getShields()+$amount);
	}
	
	public function getOldShields()
	{
		return $this->getOldHardware(HARDWARE_SHIELDS);
	}
	
	public function setOldShields($amount)
	{
		$this->setOldHardware(HARDWARE_SHIELDS,$amount);
	}
	
	public function hasShields()
	{
		return $this->getShields()>0;
	}
	
	public function hasLostShields()
	{
		return $this->getShields()<$this->getOldShields();
	}
	
	public function hasMaxShields()
	{
		return $this->getShields()==$this->getMaxShields();
	}
	
	public function getMaxShields()
	{
		return $this->getMaxHardware(HARDWARE_SHIELDS);
	}
	
	public function getArmour()
	{
		return $this->getHardware(HARDWARE_ARMOUR);
	}
	
	public function setArmour($amount,$updateOldAmount=false)
	{
		if($updateOldAmount && !$this->hasLostArmour())
			$this->setOldHardware(HARDWARE_ARMOUR,$amount);
		$this->setHardware(HARDWARE_ARMOUR,$amount);
	}
	
	public function decreaseArmour($amount)
	{
		$this->setArmour($this->getArmour()-$amount);
	}
	
	public function increaseArmour($amount)
	{
		$this->setArmour($this->getArmour()+$amount);
	}
	
	public function getOldArmour()
	{
		return $this->getOldHardware(HARDWARE_ARMOUR);
	}
	
	public function setOldArmour($amount)
	{
		$this->setOldHardware(HARDWARE_ARMOUR,$amount);
	}
	
	public function hasArmour()
	{
		return $this->getArmour()>0;
	}
	
	public function hasLostArmour()
	{
		return $this->getArmour()<$this->getOldArmour();
	}
	
	public function hasMaxArmour()
	{
		return $this->getArmour()==$this->getMaxArmour();
	}
	
	public function getMaxArmour()
	{
		return $this->getMaxHardware(HARDWARE_ARMOUR);
	}
	
	public function isDead()
	{
		return !$this->hasArmour()&&!$this->hasShields();
	}
	
	public function canAcceptCDs()
	{
		return $this->getCDs()<$this->getMaxCDs();
	}
	
	public function canAcceptSDs()
	{
		return $this->getSDs()<$this->getMaxSDs();
	}
	
	public function canAcceptMines()
	{
		return $this->getMines()<$this->getMaxMines();
	}
	
	public function hasCDs()
	{
		return $this->getCDs()>0;
	}
	
	public function hasSDs()
	{
		return $this->getSDs()>0;
	}
	
	public function hasMines()
	{
		return $this->getMines()>0;
	}
	
	public function getCDs()
	{
		return $this->getHardware(HARDWARE_COMBAT);
	}
	
	public function setCDs($amount,$updateOldAmount=false)
	{
		if($updateOldAmount && !$this->hasLostCDs())
			$this->setOldHardware(HARDWARE_COMBAT,$amount);
		$this->setHardware(HARDWARE_COMBAT,$amount);
	}
	
	public function decreaseCDs($amount,$updateOldAmount=false)
	{
		$this->setCDs($this->getCDs()-$amount,$updateOldAmount);
	}
	
	public function increaseCDs($amount)
	{
		$this->setCDs($this->getCDs()+$amount);
	}
	
	public function getOldCDs()
	{
		return $this->getOldHardware(HARDWARE_COMBAT);
	}
	
	public function setOldCDs($amount)
	{
		$this->setOldHardware(HARDWARE_COMBAT,$amount);
	}
	
	public function hasLostCDs()
	{
		return $this->getCDs()<$this->getOldCDs();
	}
	
	public function getMaxCDs()
	{
		return $this->getMaxHardware(HARDWARE_COMBAT);
	}
	
	public function getSDs()
	{
		return $this->getHardware(HARDWARE_SCOUT);
	}
	
	public function setSDs($amount)
	{
		$this->setHardware(HARDWARE_SCOUT,$amount);
	}
	
	public function decreaseSDs($amount)
	{
		$this->setSDs($this->getSDs()-$amount);
	}
	
	public function increaseSDs($amount)
	{
		$this->setSDs($this->getSDs()+$amount);
	}
	
	public function getMaxSDs()
	{
		return $this->getMaxHardware(HARDWARE_SCOUT);
	}
	
	public function getMines()
	{
		return $this->getHardware(HARDWARE_MINE);
	}
	
	public function setMines($amount)
	{
		$this->setHardware(HARDWARE_MINE,$amount);
	}
	
	public function decreaseMines($amount)
	{
		$this->setMines($this->getMines()-$amount);
	}
	
	public function increaseMines($amount)
	{
		$this->setMines($this->getMines()+$amount);
	}
	
	public function getMaxMines()
	{
		return $this->getMaxHardware(HARDWARE_MINE);
	}
	
	public function getCargoHolds()
	{
		return $this->getHardware(HARDWARE_CARGO);
	}
	
	public function setCargoHolds($amount)
	{
		$this->setHardware(HARDWARE_CARGO,$amount);
	}
	
	public function &getCargo($goodID=false)
	{
		if($goodID!==false)
		{
			if(isset($this->cargo[$goodID]))
				return $this->cargo[$goodID];
			$cargo = 0;
			return $cargo;
		}
		return $this->cargo;
	}
	
	public function hasCargo($goodID=false)
	{
		if($goodID!==false)
			return $this->getCargo($goodID)>0;
		if(is_array($cargo = $this->getCargo()))
			return array_sum($cargo)>0;
		return false;
	}
	
	public function setCargo($goodID,$amount)
	{
		if($this->getCargo($goodID) == $amount)
			return;
		$this->cargo[$goodID] = $amount;
		$this->hasChangedCargo = true;
	}
	
	public function decreaseCargo($goodID,$amount)
	{
		if($amount < 0)
			throw new Exception('Trying to decrease negative cargo.');
		$this->setCargo($goodID,$this->getCargo($goodID)-$amount);
	}
	
	public function increaseCargo($goodID,$amount)
	{
		if($amount < 0)
			throw new Exception('Trying to increase negative cargo.');
		$this->setCargo($goodID,$this->getCargo($goodID)+$amount);
	}
	
	public function getEmptyHolds()
	{
		return $this->getCargoHolds()-$this->getUsedHolds();
	}
	
	public function getUsedHolds()
	{
		return array_sum($this->getCargo());
	}
	
	public function hasMaxCargoHolds()
	{
		return $this->getCargoHolds()==$this->getMaxCargoHolds();
	}
	
	public function getMaxCargoHolds()
	{
		return $this->getMaxHardware(HARDWARE_CARGO);
	}
	
	
	public function getMaxMiningDrones()
	{
		return $this->getMaxHardware(HARDWARE_CARGO);
	}
	
	public function getMiningDrones()
	{
		$miningDrones = array();
		if(is_array($hardware = $this->getHardware()))
			foreach ($hardware as $hardwareTypeID => $amount)
			{
				if(hardware_is_mining_drone($hardwareTypeID))
					$hardwareTypeID[$hardwareTypeID] = $hardwareTypeID;
			}
		return $miningDrones;
	}
	
	
	public function isUnderAttack()
	{
		return $this->hasLostShields() || $this->hasLostArmour() || $this->hasLostCDs();
	}
	
	public function removeUnderAttack()
	{
		global $var;
		$underAttack = $this->isUnderAttack();
		$this->setOldShields($this->getShields());
		$this->setOldCDs($this->getCDs());
		$this->setOldArmour($this->getArmour());
		if(isset($var['UnderAttack']))
			return $var['UnderAttack'];
		if($underAttack && !USING_AJAX)
		{
			SmrSession::updateVar('UnderAttack',$underAttack); //Remember we are under attack for AJAX
		}
		return $underAttack;
	}
	
	public function hasWeapons()
	{
		return $this->getNumWeapons()>0;
	}
	
	public function &getWeapons()
	{
		return $this->weapons;
	}
	
	public function canAttack()
	{
		return $this->hasWeapons() || $this->hasCDs();
	}
	
	public function getNumWeapons()
	{
		return count($this->getWeapons());
	}
	
	public function getOpenWeaponSlots()
	{
		return $this->getHardpoints()-$this->getNumWeapons();
	}
	
	public function hasOpenWeaponSlots()
	{
		return $this->getOpenWeaponSlots()>0;
	}
	
	public function getHardpoints()
	{
		return $this->baseShip['Hardpoint'];
	}
	
	public function getTotalShieldDamage()
	{
		$weapons = $this->getWeapons();
		$shieldDamage = 0;
		foreach($weapons as &$weapon)
		{
			$shieldDamage+=$weapon->getShieldDamage();
		}
		return $shieldDamage;
	}
	
	public function getTotalArmourDamage()
	{
		$weapons = $this->getWeapons();
		$armourDamage = 0;
		foreach($weapons as &$weapon)
		{
			$armourDamage+=$weapon->getArmourDamage();
		}
		return $armourDamage;
	}
	
	public function getTotalUpgradeSpace()
	{
		//TODO
		return 0;
	}
	
	public function getUsedUpgradeSpace()
	{
		//TODO
		return 0;
	}
	
	public function getFreeUpgradeSpace()
	{
		return $this->getTotalUpgradeSpace()-$this->getUsedUpgradeSpace();
	}
	
	public function getLastDowngrade()
	{
		return 0;
	}
	
	public function canDowngrade()
	{
		return $this->getRemainingDowngradeWait() <= 0;
	}
	
	public function getRemainingDowngradeWait()
	{
		$downgradeWait = 24 * 3600;
		return $this->getLastDowngrade() - (TIME - $downgradeWait);
	}
	
	public function isFederal()
	{
		return $this->getShipTypeID() == 20 || $this->getShipTypeID() == 21 || $this->getShipTypeID() == 22;
	}
	
	public function &shootPlayer(AbstractSmrPlayer &$targetPlayer)
	{
		return $this->shootPlayers(array(&$targetPlayer));
	}
	
	public function &shootPlayers(array &$targetPlayers)
	{
		$thisPlayer =& $this->getPlayer();
		$results = array('Player' => &$thisPlayer, 'TotalDamage' => 0);
		if($thisPlayer->isDead())
		{
			$results['DeadBeforeShot'] = true;
			return $results;
		}
		$results['DeadBeforeShot'] = false;
		foreach($this->weapons as $orderID => &$weapon)
		{
			$results['Weapons'][$orderID] =& $weapon->shootPlayer($thisPlayer, $targetPlayers[array_rand($targetPlayers)]);
			if($results['Weapons'][$orderID]['Hit'])
				$results['TotalDamage'] += $results['Weapons'][$orderID]['ActualDamage']['TotalDamage'];
		}
		if($this->hasCDs())
		{
			$thisCDs = new SmrCombatDrones($this->getGameID(),$this->getCDs());
			$results['Drones'] =& $thisCDs->shootPlayer($thisPlayer, $targetPlayers[array_rand($targetPlayers)]);
			$results['TotalDamage'] += $results['Drones']['ActualDamage']['TotalDamage'];
		}
		$thisPlayer->increaseExperience(round($results['TotalDamage']/4)); // 1/4 weapon damage to exp.
		$thisPlayer->increaseHOF($results['TotalDamage'],array('Combat','Player','Damage Done'));
		$thisPlayer->increaseHOF(1,array('Combat','Player','Shots'));
		return $results;
	}
	
	public function &shootForces(SmrForce &$forces)
	{
		$thisPlayer =& $this->getPlayer();
		$results = array('Player' => &$thisPlayer, 'TotalDamage' => 0);
		if($thisPlayer->isDead())
		{
			$results['DeadBeforeShot'] = true;
			return $results;
		}
		$results['DeadBeforeShot'] = false;
		foreach($this->weapons as $orderID => &$weapon)
		{
			$results['Weapons'][$orderID] =& $weapon->shootForces($thisPlayer, $forces);
			if($results['Weapons'][$orderID]['Hit'])
			{
				$results['TotalDamage'] += $results['Weapons'][$orderID]['ActualDamage']['TotalDamage'];
				$thisPlayer->increaseHOF($results['Weapons'][$orderID]['ActualDamage']['NumMines'],array('Combat','Forces','Mines','Killed'));
				$thisPlayer->increaseHOF($results['Weapons'][$orderID]['ActualDamage']['Mines'],array('Combat','Forces','Mines','Damage Done'));
				$thisPlayer->increaseHOF($results['Weapons'][$orderID]['ActualDamage']['NumCDs'],array('Combat','Forces','Combat Drones','Killed'));
				$thisPlayer->increaseHOF($results['Weapons'][$orderID]['ActualDamage']['CDs'],array('Combat','Forces','Combat Drones','Damage Done'));
				$thisPlayer->increaseHOF($results['Weapons'][$orderID]['ActualDamage']['NumSDs'],array('Combat','Forces','Scout Drones','Killed'));
				$thisPlayer->increaseHOF($results['Weapons'][$orderID]['ActualDamage']['SDs'],array('Combat','Forces','Scout Drones','Damage Done'));
				$thisPlayer->increaseHOF($results['Weapons'][$orderID]['ActualDamage']['NumMines']+$results['Weapons'][$orderID]['ActualDamage']['NumCDs']+$results['Weapons'][$orderID]['ActualDamage']['NumSDs'],array('Combat','Forces','Killed'));
			}
		}
		if($this->hasCDs())
		{
			$thisCDs = new SmrCombatDrones($this->getGameID(),$this->getCDs());
			$results['Drones'] =& $thisCDs->shootForces($thisPlayer, $forces);
			$results['TotalDamage'] += $results['Drones']['ActualDamage']['TotalDamage'];
			$thisPlayer->increaseHOF($results['Drones']['ActualDamage']['NumMines'],array('Combat','Forces','Mines','Killed'));
			$thisPlayer->increaseHOF($results['Drones']['ActualDamage']['Mines'],array('Combat','Forces','Mines','Damage Done'));
			$thisPlayer->increaseHOF($results['Drones']['ActualDamage']['NumCDs'],array('Combat','Forces','Combat Drones','Killed'));
			$thisPlayer->increaseHOF($results['Drones']['ActualDamage']['CDs'],array('Combat','Forces','Combat Drones','Damage Done'));
			$thisPlayer->increaseHOF($results['Drones']['ActualDamage']['NumSDs'],array('Combat','Forces','Scout Drones','Killed'));
			$thisPlayer->increaseHOF($results['Drones']['ActualDamage']['SDs'],array('Combat','Forces','Scout Drones','Damage Done'));
			$thisPlayer->increaseHOF($results['Drones']['ActualDamage']['NumMines']+$results['Drones']['ActualDamage']['NumCDs']+$results['Drones']['ActualDamage']['NumSDs'],array('Combat','Forces','Killed'));
		}
		$thisPlayer->increaseExperience(round($results['TotalDamage']/20)); // 1/20 weapon damage to exp.
		$thisPlayer->increaseHOF($results['TotalDamage'],array('Combat','Forces','Damage Done'));
		$thisPlayer->increaseHOF(1,array('Combat','Forces','Shots'));
		return $results;
	}
	
	public function &shootPort(SmrPort &$port)
	{
		$thisPlayer =& $this->getPlayer();
		$results = array('Player' => &$thisPlayer, 'TotalDamage' => 0);
		if($thisPlayer->isDead())
		{
			$results['DeadBeforeShot'] = true;
			return $results;
		}
		$results['DeadBeforeShot'] = false;
		foreach($this->weapons as $orderID => &$weapon)
		{
			$results['Weapons'][$orderID] =& $weapon->shootPort($thisPlayer, $port);
			if($results['Weapons'][$orderID]['Hit'])
				$results['TotalDamage'] += $results['Weapons'][$orderID]['ActualDamage']['TotalDamage'];
		}
		if($this->hasCDs())
		{
			$thisCDs = new SmrCombatDrones($this->getGameID(),$this->getCDs());
			$results['Drones'] =& $thisCDs->shootPort($thisPlayer, $port);
			$results['TotalDamage'] += $results['Drones']['ActualDamage']['TotalDamage'];
		}
		$thisPlayer->increaseExperience(round($results['TotalDamage']/20)); // 1/20 weapon damage to exp.
		$thisPlayer->increaseHOF($results['TotalDamage'],array('Combat','Port','Damage Done'));
//		$thisPlayer->increaseHOF(1,array('Combat','Port','Shots')); //in SmrPortt::attackedBy()
		return $results;
	}
	
	public function &shootPlanet(SmrPlanet &$planet, $delayed)
	{
		$thisPlayer =& $this->getPlayer();
		$results = array('Player' => &$thisPlayer, 'TotalDamage' => 0);
		if($thisPlayer->isDead())
		{
			$results['DeadBeforeShot'] = true;
			return $results;
		}
		$results['DeadBeforeShot'] = false;
		foreach($this->weapons as $orderID => &$weapon)
		{
			$results['Weapons'][$orderID] =& $weapon->shootPlanet($thisPlayer, $planet, $delayed);
			if($results['Weapons'][$orderID]['Hit'])
				$results['TotalDamage'] += $results['Weapons'][$orderID]['ActualDamage']['TotalDamage'];
		}
		if($this->hasCDs())
		{
			$thisCDs = new SmrCombatDrones($this->getGameID(),$this->getCDs());
			$results['Drones'] =& $thisCDs->shootPlanet($thisPlayer, $planet, $delayed);
			$results['TotalDamage'] += $results['Drones']['ActualDamage']['TotalDamage'];
		}
		$thisPlayer->increaseExperience(round($results['TotalDamage']/20)); // 1/20 weapon damage to exp.
		$thisPlayer->increaseHOF($results['TotalDamage'],array('Combat','Planet','Damage Done'));
//		$thisPlayer->increaseHOF(1,array('Combat','Planet','Shots')); //in SmrPlanet::attackedBy()
		return $results;
	}
	
	public function &doWeaponDamage(array $damage)
	{
		$alreadyDead = $this->getPlayer()->isDead();
		$armourDamage = 0;
		$cdDamage = 0;
		$shieldDamage = 0;
		if(!$alreadyDead)
		{
			$shieldDamage = $this->doShieldDamage(min($damage['MaxDamage'],$damage['Shield']));
			$damage['MaxDamage'] -= $shieldDamage;
			if(!$this->hasShields() && ($shieldDamage == 0 || $damage['Rollover']))
			{
				$cdDamage = $this->doCDDamage(min($damage['MaxDamage'],$damage['Armour']));
				$damage['Armour'] -= $cdDamage;
				$damage['MaxDamage'] -= $cdDamage;
				if(!$this->hasCDs() && ($cdDamage == 0 || $damage['Rollover']))
					$armourDamage = $this->doArmourDamage(min($damage['MaxDamage'],$damage['Armour']));
			}
		}
		$return = array(
						'KillingShot' => !$alreadyDead && $this->isDead(),
						'TargetAlreadyDead' => $alreadyDead,
						'Shield' => $shieldDamage,
						'CDs' => $cdDamage,
						'NumCDs' => $cdDamage/CD_ARMOUR,
						'Armour' => $armourDamage,
						'HasCDs' => $this->hasCDs(),
						'TotalDamage' => $shieldDamage+$cdDamage+$armourDamage
		);
		return $return;
	}
	
	public function &doMinesDamage(array $damage)
	{
		$alreadyDead = $this->getPlayer()->isDead();
		$armourDamage = 0;
		$cdDamage = 0;
		$shieldDamage = 0;
		if(!$alreadyDead)
		{
			$shieldDamage = $this->doShieldDamage(min($damage['MaxDamage'],$damage['Shield']));
			$damage['MaxDamage'] -= $shieldDamage;
			if(!$this->hasShields() && ($shieldDamage == 0 || $damage['Rollover']))
			{ //skip CDs if it's mines
				$armourDamage = $this->doArmourDamage(min($damage['MaxDamage'],$damage['Armour']));
			}
		}
		$return = array(
						'KillingShot' => !$alreadyDead && $this->isDead(),
						'TargetAlreadyDead' => $alreadyDead,
						'Shield' => $shieldDamage,
						'CDs' => $cdDamage,
						'NumCDs' => $cdDamage/CD_ARMOUR,
						'Armour' => $armourDamage,
						'HasCDs' => $this->hasCDs(),
						'TotalDamage' => $shieldDamage+$cdDamage+$armourDamage
		);
		return $return;
	}
	
	protected function doShieldDamage($damage)
	{
		$actualDamage = min($this->getShields(),$damage);
		$this->decreaseShields($actualDamage);
		return $actualDamage;
	}
	
	protected function doCDDamage($damage)
	{
		$actualDamage = min($this->getCDs(),floor($damage/CD_ARMOUR));
		$this->decreaseCDs($actualDamage);
		return $actualDamage*CD_ARMOUR;
	}
	
	protected function doArmourDamage($damage)
	{
		$actualDamage = min($this->getArmour(),$damage);
		$this->decreaseArmour($actualDamage);
		return $actualDamage;
	}
	
	public function getMR()
	{
		return round(
						700 - 
						(
							(
								$this->getShields()
								+$this->getArmour()
								+$this->getCDs()*3
							)/25
							+(
								$this->getCargoHolds()/100
								-$this->getSpeed()*5
								+($this->getHardpoints()/*+$ship['Increases']['Ship Power']*/)*5
								/*+(
									$ship['Increases']['Mines']
									+$ship['Increases']['Scout Drones']
								)/12*/
								+$this->getCDs()/5
							)
						)
//						+ $player->getStat('Maneuverability') * $MANEUV_MOD
					);
	}
	
	public function getUpgradeShipHREF()
	{
		if(!isset($this->upgradeShipHREF))
		{
			$container = array();
			$container['url']		= 'skeleton.php';
			$container['body']		= 'ship_upgrade.php';
			$this->upgradeShipHREF = SmrSession::get_new_href($container);
		}
		return $this->upgradeShipHREF;
	}
}

?>