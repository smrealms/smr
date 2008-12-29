<?
require_once('SmrMySqlDatabase.class.inc');

require_once('AbstractSmrPlayer.class.inc');
require_once('SmrWeapon.class.inc');
require_once('SmrCombatDrones.class.inc');
abstract class AbstractSmrShip
{
	protected static $CACHE_BASE_SHIPS = array();
	
	protected $player;
	
	protected $gameID;
	protected $baseShip;
	
	protected $hardware;
	protected $oldHardware;
	
	protected $cargo;
	protected $cargo_left;
	
	protected $weapons;
	
	protected $illusionShip;
	
	protected $hasChangedWeapons;
	protected $hasChangedCargo;
	protected $hasChangedHardware;

	public static function &getBaseShip($gameTypeID,$shipTypeID,$forceUpdate = false)
	{
		if($forceUpdate || !isset(self::$CACHE_BASE_SHIPS[$gameTypeID . ':' . $shipTypeID]))
		{
			// determine ship
			$db = new SmrMySqlDatabase();
			$db->query('SELECT * FROM ship_type WHERE ship_type_id = '.$shipTypeID.' LIMIT 1'); //TODO add game type id
			$db->nextRecord();
			self::$CACHE_BASE_SHIPS[$gameTypeID . ':' . $shipTypeID] =& self::buildBaseShip($db);
		}
		return self::$CACHE_BASE_SHIPS[$gameTypeID. ':' .$shipTypeID];
	}
	
	protected static function &buildBaseShip(Database &$db)
	{
		$ship = array();
		$ship['Name']		= $db->getField('ship_name');
		$ship['ShipTypeID']	= $db->getField('ship_type_id');
		$ship['Hardpoint']	= $db->getField('hardpoint');
		$ship['Speed']		= $db->getField('speed');
		$ship['Cost']		= $db->getField('cost');
		
		// get supported hardware from db
		$db2 = new SmrMySqlDatabase();
		$db2->query('SELECT hardware_type_id, max_amount FROM ship_type_support_hardware ' .
			'WHERE ship_type_id = '.$ship['ShipTypeID']);
		
		while ($db2->nextRecord())
		{
			// adding hardware to array
			$ship['MaxHardware'][$db2->getField('hardware_type_id')] = $db2->getField('max_amount');
		}
		return $ship;
	}

	public static function &getAllBaseShips($gameTypeID,$forceUpdate = false)
	{
		// determine ship
		$db = new SmrMySqlDatabase();
		$db->query('SELECT * FROM ship_type'); //TODO add game type id
		while($db->nextRecord())
			if(!isset(self::$CACHE_BASE_SHIPS[$gameTypeID . ':' . $db->getField('ship_type_id')]))
				self::$CACHE_BASE_SHIPS[$gameTypeID . ':' . $db->getField('ship_type_id')] =& self::buildBaseShip($db);
		return self::$CACHE_BASE_SHIPS;
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
	}
	
	public function checkForExcessWeapons()
	{
		while ($this->hasWeapons() && ($this->getPowerUsed() > $this->getMaxPower() || $this->getNumWeapons() > $this->getHardpoints()))
		{
			//erase the first weapon 1 at a time until we are okay
			$this->removeWeapon(1);
		}
	}
	
	public function getPowerUsed()
	{
		$power = 0;
		foreach ($this->weapons as &$weapon)
			$power += $weapon->getPowerLevel();
		$power=0;
	}
	
	public function getMaxPower()
	{
		return 50;
	}
	
	public function hasIllegalGoods()
	{
		return $ship->hasCargo(5) || $ship->hasCargo(9) || $ship->hasCargo(12);
	}
	
	public function getAttackRating()
	{
		return round(($this->getTotalShieldDamage() + $this->getTotalArmourDamage() + $this->getCDs() * .7) / 40);
	}
	
	public function getDefenseRating()
	{
		return round((($this->getShields() + $this->getArmour()) / 100) + (($this->getCDs() * 3) / 100));
	}
	
	function shield_low() { return floor($this->getShields() / 100) * 100; }
	function shield_high() { return $this->shield_low() + 100; }
	function armor_low() { return floor($this->getArmour() / 100) * 100; }
	function armor_high() { return $this->armor_low() + 100; }
	function combat_drones_low() { return floor($this->getCDs() / 100) * 100; }
	function combat_drones_high() { return $this->combat_drones_low() + 100; }
	
	public function &addWeapon($weaponTypeID)
	{
		$weapon =& SmrWeapon::getWeapon($this->getGameID(),$weaponTypeID);
		$this->weapons[] = $weapon;
		$this->hasChangedWeapons = true;
		return $weapon;
	}
	
	public function moveWeaponUp($orderID)
	{
		$replacement = $orderID - 1;
		if($replacement < 1)
		{
			// Shift everything up by one and put the selected weapon at the bottom
			$temp =& $this->weapons[$orderID];
			for($i=1;$i<count($this->weapons);++$i)
			{
				$this->weapons[$i] =& $this->weapons[$i+1];
			}
			$this->weapon[count($this->weapons)] =& $temp;
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
		if($replacement > count($this->weapons))
		{
			// Shift everything down by one and put the selected weapon at the top
			$temp =& $this->weapons[count($this->weapons)];
			for($i=count($this->weapons);$i>1;--$i) {
				$this->weapons[$i] =& $this->weapons[$i-1];
			}	
			$this->weapons[1] =& $temp;
		}
		else
		{
			$temp =& $this->weapons[$replacement];
			$this->weapons[$replacement] =& $this->weapons[$orderID];
			$this->weapons[$orderID] =& $temp;
		}
		$this->hasChangedWeapons = true;
	}
	
	function removeWeapon($orderID)
	{
		// Shift everything after the removed weapon up by one and put the selected weapon at the bottom
		for($i=$orderID;$i<count($this->weapons);++$i)
		{
			$this->weapons[$i] =& $this->weapons[$i+1];
		} unset($this->weapons[count($this->weapons)]);
		$this->hasChangedWeapons = true;
	}
	
	function removeAllWeapons()
	{
		$this->weapons = array();
		$this->hasChangedWeapons = true;
	}
	
	function removeAllCargo()
	{
		$this->cargo = array();
		$this->hasChangedCargo = true;
	}
	
	function removeAllHardware() {
		
		$this->hardware = array();
		$this->decloak();
		
		$this->db->query('DELETE FROM ship_has_illusion WHERE account_id = '.$this->getAccountID().' AND ' .
			'game_id = '.$this->getGameID());
		$this->hasChangedHardware = true;
	}
	
	function get_pod()
	{
		$this->removeAllWeapons();
		$this->removeAllCargo();
		$this->removeAllHardware();
		
		$this->hardware[1] = 50;
		$this->hardware[2] = 50;
		$this->hardware[3] = 5;
		$this->update_hardware();
		
		$this->setShipTypeID(69);
		$this->update();
		
		$this->mark_seen();
		
	}
	
	function mark_seen() {
		foreach ($this->hardware as $hardware_type_id => $amount) {
			$this->db->query('UPDATE ship_has_hardware SET old_amount = '.$amount.' ' .
				'WHERE account_id = '.$this->getAccountID().' AND ' .
				'game_id = '.$this->getGameID().' AND ' .
				'hardware_type_id = '.$hardware_type_id.' LIMIT 1');
		}
	}
	
	public function isCloaked()
	{
		if(!$this->hasCloak())
			return false;
		if(!isset($this->isCloaked))
		{
			$this->db->query('SELECT * FROM ship_is_cloaked ' .
				'WHERE account_id = '.$this->getAccountID().' AND ' .
				'game_id = '.$this->getGameID().' LIMIT 1');
			$this->isCloaked = $this->db->getNumRows()>0;
		}
		return $this->isCloaked;
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
	
	abstract function decloak();
	
	abstract function enableCloak();
	
	abstract function setIllusion($ship_id, $attack, $defense);
	
	abstract function disableIllusion();
	
	function getIllusionShipID()
	{
		$this->getIllusionShip();
		return $this->illusionShip['ID'];
	}
	
	function getIllusionShipName()
	{
		$this->getIllusionShip();
		return $this->illusionShip['Name'];
	}
	
	abstract public function getIllusionShip();
	
	function getIllusionAttack()
	{
		$this->getIllusionShip();
		return $this->illusionShip['Attack'];
	}
	
	function getIllusionDefense()
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
	
	public function getSpeed()
	{
		return $this->baseShip['Speed'];
	}
	
	public function getHardware($hardwareTypeID)
	{
		return isset($this->hardware[$hardwareTypeID]) ? $this->hardware[$hardwareTypeID] : 0;
	}
	
	public function setHardware($hardwareTypeID,$amount)
	{
		if($this->hardware[$hardwareTypeID] == $amount)
			return;
		$this->hardware[$hardwareTypeID] = $amount;
		$this->hasChangedHardware = true;
	}
	
	public function increaseHardware($hardwareTypeID,$amount)
	{
		$this->setHardware($hardwareTypeID,$this->getHardware($hardwareTypeID)+$amount);
	}
	
	public function getOldHardware($hardwareTypeID)
	{
		return isset($this->oldHardware[$hardwareTypeID]) ? $this->oldHardware[$hardwareTypeID] : 0;
	}
	
	public function setOldHardware($hardwareTypeID,$amount)
	{
		if($this->oldHardware[$hardwareTypeID] == $amount)
			return;
		$this->oldHardware[$hardwareTypeID] = $amount;
		$this->hasChangedHardware = true;
	}
	
	public function getMaxHardware($hardwareTypeID)
	{
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
	
	public function getCargo($goodID=false)
	{
		if($goodID!==false)
			return $this->cargo[$goodID];
		return $this->cargo;
	}
	
	public function hasCargo($goodID=false)
	{
		if($goodID!==false)
			return $this->getCargo($goodID)>0;
		return array_sum($this->getCargo())>0;
	}
	
	public function setCargo($goodID,$amount)
	{
		if($this->cargo[$goodID] == $amount)
			return;
		$this->cargo[$goodID] = $amount;
		$this->hasChangedCargo = true;
	}
	
	public function decreaseCargo($goodID,$amount)
	{
		$this->setCargo($goodID,$this->getCargo($goodID)-$amount);
	}
	
	public function increaseCargo($goodID,$amount)
	{
		$this->setCargo($goodID,$this->getCargo($goodID)+$amount);
	}
	
	public function getEmptyHolds()
	{
		return $this->cargo_left;
	}
	
	
	public function getMaxCargoHolds()
	{
		return $this->getMaxHardware(HARDWARE_CARGO);
	}
	
	public function isUnderAttack()
	{
		return $this->hasLostShields() || $this->hasLostArmour() || $this->hasLostCDs();
	}
	
	public function removeUnderAttack()
	{
		$underAttack = $this->isUnderAttack();
		$this->setOldShields($this->getShields());
		$this->setOldCDs($this->getCDs());
		$this->setOldArmour($this->getArmour());
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
	
	public function getNumWeapons()
	{
		return count($this->getWeapons());
	}
	
	public function getOpenWeaponSlots()
	{
		return $this->getHardpoints()-$this->getNumWeapons();
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
			$results['DeadBeforeShot'] = false;
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
		$thisPlayer->increaseExperience($results['TotalDamage']/4); // 1/4 weapon damage to exp.
		$thisPlayer->increaseHOF($results['TotalDamage'],'player_damage');
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
						'KillingShot' => $alreadyDead != $this->isDead(),
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
		$actualDamage = min($this->getCDs(),$damage/CD_ARMOUR);
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