<?
require_once('SmrMySqlDatabase.class.inc');

require_once('AbstractSmrPlayer.class.inc');
require_once('SmrForce.class.inc');
abstract class AbstractSmrCombatWeapon
{
	protected $gameTypeID;
	protected $name;
	protected $raceID;
	protected $maxDamage;
	protected $shieldDamage;
	protected $armourDamage;
	protected $accuracy;
	protected $damageRollover;
	protected $raidWeapon;
	
	public function getBaseAccuracy()
	{
		return $this->accuracy;
	}
	
	public function getName()
	{
		return $this->name;
	}
	
	public function getMaxDamage()
	{
		return $this->maxDamage;
	}
	
	public function getShieldDamage()
	{
		return $this->shieldDamage;
	}
	
	public function getArmourDamage()
	{
		return $this->armourDamage;
	}
	
	public function isDamageRollover()
	{
		return $this->damageRollover;
	}
	
	public function isRaidWeapon()
	{
		return $this->raidWeapon;
	}
	
	public function canShootForces()
	{
		return !$this->isRaidWeapon();
	}
	
	public function canShootTraders()
	{
		return !$this->isRaidWeapon();
	}
	
	public function getDamage()
	{
		return array('MaxDamage' => $this->getMaxDamage(), 'Shield' => $this->getShieldDamage(), 'Armour' => $this->getArmourDamage(), 'Rollover' => $this->isDamageRollover());
	}
	
	abstract public function &getModifiedDamageAgainstForces(AbstractSmrPlayer &$weaponPlayer, SmrForce &$forces);
	abstract public function &getModifiedDamageAgainstPlayer(AbstractSmrPlayer &$weaponPlayer, AbstractSmrPlayer &$targetPlayer);
	abstract public function &getModifiedForceDamageAgainstPlayer(SmrForce &$forces, AbstractSmrPlayer &$targetPlayer);
	
	protected function &doPlayerDamageToForce(array &$return, AbstractSmrPlayer &$weaponPlayer, SmrForce &$forces)
	{
		$return['WeaponDamage'] =& $this->getModifiedDamageAgainstForces($weaponPlayer,$forces);
		$return['ActualDamage'] =& $forces->doWeaponDamage($return['WeaponDamage']);
		if($return['ActualDamage']['KillingShot'])
			$return['KillResults'] =& $forces->killForcesByPlayer($weaponPlayer);
		return $return;
	}
	
	protected function &doPlayerDamageToPlayer(array &$return, AbstractSmrPlayer &$weaponPlayer, AbstractSmrPlayer &$targetPlayer)
	{
		$return['WeaponDamage'] =& $this->getModifiedDamageAgainstPlayer($weaponPlayer,$targetPlayer);
		$return['ActualDamage'] =& $targetPlayer->getShip()->doWeaponDamage($return['WeaponDamage']);

		if($return['ActualDamage']['KillingShot'])
			$return['KillResults'] =& $targetPlayer->killPlayerByPlayer($weaponPlayer);
		return $return;
	}
	
	protected function &doForceDamageToPlayer(array &$return, SmrForce &$forces, AbstractSmrPlayer &$targetPlayer)
	{
		$return['WeaponDamage'] =& $this->getModifiedForceDamageAgainstPlayer($forces,$targetPlayer);
		$return['ActualDamage'] =& $targetPlayer->getShip()->doWeaponDamage($return['WeaponDamage']);

		if($return['ActualDamage']['KillingShot'])
			$return['KillResults'] =& $targetPlayer->killPlayerByForces($forces);
		return $return;
	}
	
	abstract public function &shootForces(AbstractSmrPlayer &$weaponPlayer, SmrForce &$forces);
	abstract public function &shootPlayer(AbstractSmrPlayer &$weaponPlayer, AbstractSmrPlayer &$targetPlayer);
	abstract public function &shootPlayerAsForce(SmrForce &$forces, AbstractSmrPlayer &$targetPlayer);
}

?>