<?php
interface CombatWeapon
{
	public function getBaseAccuracy();
	
	public function getModifiedAccuracy(AbstractSmrPlayer &$weaponPlayer);
	
	public function getModifiedAccuracyAgainstPlayer(AbstractSmrPlayer &$weaponPlayer, AbstractSmrPlayer &$targetPlayer);
	
	public function getWeaponTypeID();
	
	public function getName();
	
	public function getShieldDamage();
	
	public function getArmourDamage();
	
	public function isDamageRollover();
	
	public function isRaidWeapon();
	
	public function canShootTraders();
	
	public function getDamage();
	
	public function &getModifiedDamage(AbstractSmrPlayer &$weaponPlayer);
	
	public function &getModifiedDamageAgainstPlayer(AbstractSmrPlayer &$weaponPlayer, AbstractSmrPlayer &$targetPlayer);
	
	public function &shootPlayer(AbstractSmrPlayer &$weaponPlayer, AbstractSmrPlayer &$targetPlayer);
}
?>
