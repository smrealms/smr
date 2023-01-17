<?php declare(strict_types=1);

namespace Smr\Combat\Weapon;

use Exception;
use Smr\AbstractPlayer;
use Smr\Force;
use Smr\Planet;
use Smr\Port;

class CombatDrones extends AbstractWeapon {

	use ForcesTrait;

	protected const MAX_CDS_RAND = 54;

	public function __construct(int $numberOfCDs, bool $portPlanetDrones = false) {
		$this->amount = $numberOfCDs;
		$this->name = 'Combat Drones';
		if ($portPlanetDrones === false) {
			$this->shieldDamage = 2;
			$this->armourDamage = 2;
		} else {
			$this->shieldDamage = 1;
			$this->armourDamage = 1;
		}
		$this->accuracy = 3;
		$this->damageRollover = true;
	}

	public function getModifiedAccuracy(): float {
		return $this->getBaseAccuracy();
	}

	protected function getModifiedAccuracyAgainstForcesUsingRandom(AbstractPlayer $weaponPlayer, Force $forces, int $random): float {
		$modifiedAccuracy = $this->getModifiedAccuracy();
		$modifiedAccuracy += $random;

		return max(0, min(100, $modifiedAccuracy));
	}

	public function getModifiedAccuracyAgainstForces(AbstractPlayer $weaponPlayer, Force $forces): float {
		return $this->getModifiedAccuracyAgainstForcesUsingRandom($weaponPlayer, $forces, rand(3, self::MAX_CDS_RAND));
	}

	protected function getModifiedAccuracyAgainstPortUsingRandom(AbstractPlayer $weaponPlayer, Port $port, int $random): float {
		$modifiedAccuracy = $this->getModifiedAccuracy();
		$modifiedAccuracy += $random;

		return max(0, min(100, $modifiedAccuracy));
	}

	public function getModifiedAccuracyAgainstPort(AbstractPlayer $weaponPlayer, Port $port): float {
		return $this->getModifiedAccuracyAgainstPortUsingRandom($weaponPlayer, $port, rand(3, self::MAX_CDS_RAND));
	}

	protected function getModifiedAccuracyAgainstPlanetUsingRandom(AbstractPlayer $weaponPlayer, Planet $planet, int $random): float {
		$modifiedAccuracy = $this->getModifiedAccuracy();
		$modifiedAccuracy += $random;

		return max(0, min(100, $modifiedAccuracy));
	}

	public function getModifiedAccuracyAgainstPlanet(AbstractPlayer $weaponPlayer, Planet $planet): float {
		return $this->getModifiedAccuracyAgainstPlanetUsingRandom($weaponPlayer, $planet, rand(3, self::MAX_CDS_RAND));
	}

	public function getModifiedAccuracyAgainstPlayer(AbstractPlayer $weaponPlayer, AbstractPlayer $targetPlayer): float {
		return $this->getModifiedAccuracyAgainstPlayerUsingRandom($weaponPlayer, $targetPlayer, rand(3, self::MAX_CDS_RAND));
	}

	protected function getModifiedAccuracyAgainstPlayerUsingRandom(AbstractPlayer $weaponPlayer, AbstractPlayer $targetPlayer, int $random): float {
		$modifiedAccuracy = $this->getModifiedAccuracy();
		$levelRand = rand(IFloor($weaponPlayer->getLevelID() / 2), $weaponPlayer->getLevelID());
		$modifiedAccuracy += ($random + $levelRand - ($targetPlayer->getLevelID() - $weaponPlayer->getLevelID()) / 3) / 1.5;

		$weaponShip = $weaponPlayer->getShip();
		$targetShip = $targetPlayer->getShip();
		$mrDiff = $targetShip->getMR() - $weaponShip->getMR();
		if ($mrDiff > 0) {
			$modifiedAccuracy -= $this->getBaseAccuracy() * ($mrDiff / MR_FACTOR) / 100;
		}

		return max(0, min(100, $modifiedAccuracy));
	}

	public function getModifiedForceAccuracyAgainstPlayer(Force $forces, AbstractPlayer $targetPlayer): float {
		return $this->getModifiedForceAccuracyAgainstPlayerUsingRandom($forces, $targetPlayer, rand(3, self::MAX_CDS_RAND));
	}

	protected function getModifiedForceAccuracyAgainstPlayerUsingRandom(Force $forces, AbstractPlayer $targetPlayer, int $random): float {
		$modifiedAccuracy = $this->getModifiedAccuracy();
		$modifiedAccuracy += $random;

		return max(0, min(100, $modifiedAccuracy));
	}

	protected function getModifiedPortAccuracyAgainstPlayer(Port $port, AbstractPlayer $targetPlayer): float {
		return 100;
	}

	protected function getModifiedPlanetAccuracyAgainstPlayer(Planet $planet, AbstractPlayer $targetPlayer): float {
		return 100;
	}

	public function getModifiedDamageAgainstForces(AbstractPlayer $weaponPlayer, Force $forces): array {
		if (!$this->canShootForces()) {
			// If we can't shoot forces then just return a damageless array and don't waste resources calculated any damage mods.
			return ['Shield' => 0, 'Armour' => 0, 'Rollover' => $this->isDamageRollover()];
		}
		$damage = $this->getDamage();
		$damage['Launched'] = ICeil($this->getAmount() * $this->getModifiedAccuracyAgainstForces($weaponPlayer, $forces) / 100);
		$damage['Kamikaze'] = 0;
		if ($weaponPlayer->isCombatDronesKamikazeOnMines()) { // If kamikaze then damage is same as MINE_ARMOUR
			$damage['Kamikaze'] = min($damage['Launched'], $forces->getMines());
			$damage['Launched'] -= $damage['Kamikaze'];
		}
		$damage['Shield'] = ICeil($damage['Launched'] * $damage['Shield']);
		$damage['Armour'] = ICeil($damage['Launched'] * $damage['Armour']);

		$damage['Launched'] += $damage['Kamikaze'];
		$damage['Shield'] += $damage['Kamikaze'] * MINE_ARMOUR;
		$damage['Armour'] += $damage['Kamikaze'] * MINE_ARMOUR;

		return $damage;
	}

	public function getModifiedDamageAgainstPort(AbstractPlayer $weaponPlayer, Port $port): array {
		if (!$this->canShootPorts()) {
			// If we can't shoot forces then just return a damageless array and don't waste resources calculated any damage mods.
			return ['Shield' => 0, 'Armour' => 0, 'Rollover' => $this->isDamageRollover()];
		}
		$damage = $this->getDamage();
		$damage['Launched'] = ICeil($this->getAmount() * $this->getModifiedAccuracyAgainstPort($weaponPlayer, $port) / 100);
		$damage['Shield'] = ICeil($damage['Launched'] * $damage['Shield']);
		$damage['Armour'] = ICeil($damage['Launched'] * $damage['Armour']);

		return $damage;
	}

	public function getModifiedDamageAgainstPlanet(AbstractPlayer $weaponPlayer, Planet $planet): array {
		if (!$this->canShootPlanets()) {
			// If we can't shoot forces then just return a damageless array and don't waste resources calculated any damage mods.
			return ['Shield' => 0, 'Armour' => 0, 'Rollover' => $this->isDamageRollover()];
		}
		$damage = $this->getDamage();
		$damage['Launched'] = ICeil($this->getAmount() * $this->getModifiedAccuracyAgainstPlanet($weaponPlayer, $planet) / 100);
		$planetMod = self::PLANET_DAMAGE_MOD;
		$damage['Shield'] = ICeil($damage['Launched'] * $damage['Shield'] * $planetMod);
		$damage['Armour'] = ICeil($damage['Launched'] * $damage['Armour'] * $planetMod);

		return $damage;
	}

	public function getModifiedDamageAgainstPlayer(AbstractPlayer $weaponPlayer, AbstractPlayer $targetPlayer): array {
		if (!$this->canShootTraders()) { // If we can't shoot traders then just return a damageless array and don't waste resources calculated any damage mods.
			return ['Shield' => 0, 'Armour' => 0, 'Rollover' => $this->isDamageRollover()];
		}
		$damage = $this->getDamage();
		$dcsMod = $targetPlayer->getShip()->hasDCS() ? DCS_PLAYER_DAMAGE_DECIMAL_PERCENT : 1;
		$damage['Launched'] = ICeil($this->getAmount() * $this->getModifiedAccuracyAgainstPlayer($weaponPlayer, $targetPlayer) / 100);
		$damage['Shield'] = ICeil($damage['Launched'] * $damage['Shield'] * $dcsMod);
		$damage['Armour'] = ICeil($damage['Launched'] * $damage['Armour'] * $dcsMod);
		return $damage;
	}

	public function getModifiedForceDamageAgainstPlayer(Force $forces, AbstractPlayer $targetPlayer): array {
		if (!$this->canShootTraders()) { // If we can't shoot traders then just return a damageless array and don't waste resources calculated any damage mods.
			return ['Shield' => 0, 'Armour' => 0, 'Rollover' => $this->isDamageRollover()];
		}
		$damage = $this->getDamage();
		$dcsMod = $targetPlayer->getShip()->hasDCS() ? DCS_FORCE_DAMAGE_DECIMAL_PERCENT : 1;
		$damage['Launched'] = ICeil($this->getAmount() * $this->getModifiedForceAccuracyAgainstPlayer($forces, $targetPlayer) / 100);
		$damage['Shield'] = ICeil($damage['Launched'] * $damage['Shield'] * $dcsMod);
		$damage['Armour'] = ICeil($damage['Launched'] * $damage['Armour'] * $dcsMod);
		return $damage;
	}

	public function getModifiedPortDamageAgainstPlayer(Port $port, AbstractPlayer $targetPlayer): array {
		if (!$this->canShootTraders()) { // If we can't shoot traders then just return a damageless array and don't waste resources calculated any damage mods.
			return ['Shield' => 0, 'Armour' => 0, 'Rollover' => $this->isDamageRollover()];
		}
		$damage = $this->getDamage();
		$dcsMod = $targetPlayer->getShip()->hasDCS() ? DCS_PORT_DAMAGE_DECIMAL_PERCENT : 1;
		$damage['Launched'] = ICeil($this->getAmount() * $this->getModifiedPortAccuracyAgainstPlayer($port, $targetPlayer) / 100);
		$damage['Shield'] = ICeil($damage['Launched'] * $damage['Shield'] * $dcsMod);
		$damage['Armour'] = ICeil($damage['Launched'] * $damage['Armour'] * $dcsMod);
		return $damage;
	}

	public function getModifiedPlanetDamageAgainstPlayer(Planet $planet, AbstractPlayer $targetPlayer): array {
		if (!$this->canShootTraders()) { // If we can't shoot traders then just return a damageless array and don't waste resources calculated any damage mods.
			return ['Shield' => 0, 'Armour' => 0, 'Rollover' => $this->isDamageRollover()];
		}
		$damage = $this->getDamage();
		$dcsMod = $targetPlayer->getShip()->hasDCS() ? DCS_PLANET_DAMAGE_DECIMAL_PERCENT : 1;
		$damage['Launched'] = ICeil($this->getAmount() * $this->getModifiedPlanetAccuracyAgainstPlayer($planet, $targetPlayer) / 100);
		$damage['Shield'] = ICeil($damage['Launched'] * $damage['Shield'] * $dcsMod);
		$damage['Armour'] = ICeil($damage['Launched'] * $damage['Armour'] * $dcsMod);
		return $damage;
	}

	/**
	 * @return array{Weapon: parent, TargetForces: \Smr\Force, Hit: bool, WeaponDamage: WeaponDamageData, ActualDamage: ForceTakenDamageData, KillResults?: array{}}
	 */
	public function shootForces(AbstractPlayer $weaponPlayer, Force $forces): array {
		$return = ['Weapon' => $this, 'TargetForces' => $forces, 'Hit' => true];
		$return = $this->doPlayerDamageToForce($return, $weaponPlayer, $forces);
		if (!isset($return['WeaponDamage']['Kamikaze'])) {
			throw new Exception('CombatDrone WeaponDamage against Force must include Kamikaze field!');
		}
		if ($return['WeaponDamage']['Kamikaze'] > 0) {
			$weaponPlayer->getShip()->decreaseCDs($return['WeaponDamage']['Kamikaze']);
		}
		return $return;
	}

	/**
	 * @return array{Weapon: parent, TargetPort: \Smr\Port, Hit: bool, WeaponDamage: WeaponDamageData, ActualDamage: TakenDamageData, KillResults?: array{}}
	 */
	public function shootPort(AbstractPlayer $weaponPlayer, Port $port): array {
		$return = ['Weapon' => $this, 'TargetPort' => $port, 'Hit' => true];
		return $this->doPlayerDamageToPort($return, $weaponPlayer, $port);
	}

	/**
	 * @return array{Weapon: parent, TargetPlanet: \Smr\Planet, Hit: bool, WeaponDamage: WeaponDamageData, ActualDamage: TakenDamageData, KillResults?: array{}}
	 */
	public function shootPlanet(AbstractPlayer $weaponPlayer, Planet $planet): array {
		$return = ['Weapon' => $this, 'TargetPlanet' => $planet, 'Hit' => true];
		return $this->doPlayerDamageToPlanet($return, $weaponPlayer, $planet);
	}

	/**
	 * @return array{Weapon: parent, TargetPlayer: \Smr\AbstractPlayer, Hit: bool, WeaponDamage: WeaponDamageData, ActualDamage: TakenDamageData, KillResults?: array{DeadExp: int, KillerExp: int, KillerCredits: int}}
	 */
	public function shootPlayer(AbstractPlayer $weaponPlayer, AbstractPlayer $targetPlayer): array {
		$return = ['Weapon' => $this, 'TargetPlayer' => $targetPlayer, 'Hit' => true];
		return $this->doPlayerDamageToPlayer($return, $weaponPlayer, $targetPlayer);
	}

	public function shootPlayerAsForce(Force $forces, AbstractPlayer $targetPlayer): array {
		$return = ['Weapon' => $this, 'TargetPlayer' => $targetPlayer, 'Hit' => true];
		return $this->doForceDamageToPlayer($return, $forces, $targetPlayer);
	}

	/**
	 * @return array{Weapon: parent, TargetPlayer: \Smr\AbstractPlayer, Hit: bool, WeaponDamage: WeaponDamageData, ActualDamage: TakenDamageData, KillResults?: array{DeadExp: int, LostCredits: int}}
	 */
	public function shootPlayerAsPort(Port $forces, AbstractPlayer $targetPlayer): array {
		$return = ['Weapon' => $this, 'TargetPlayer' => $targetPlayer, 'Hit' => true];
		return $this->doPortDamageToPlayer($return, $forces, $targetPlayer);
	}

	/**
	 * @return array{Weapon: parent, TargetPlayer: \Smr\AbstractPlayer, Hit: bool, WeaponDamage: WeaponDamageData, ActualDamage: TakenDamageData, KillResults?: array{DeadExp: int, LostCredits: int}}
	 */
	public function shootPlayerAsPlanet(Planet $forces, AbstractPlayer $targetPlayer): array {
		$return = ['Weapon' => $this, 'TargetPlayer' => $targetPlayer, 'Hit' => true];
		return $this->doPlanetDamageToPlayer($return, $forces, $targetPlayer);
	}

}
