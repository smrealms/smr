<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use Smr\AbstractPlayer;
use Smr\Page\PlayerPage;
use Smr\Planet;
use Smr\Template;

class AttackPlanet extends PlayerPage {

	public string $file = 'planet_attack.php';

	/**
	 * @param array{Attackers: array{TotalDamage: int, Downgrades: array<int, int>, Traders?: array<int, array{Player: \Smr\AbstractPlayer, TotalDamage: int, DeadBeforeShot: bool, Weapons: array<int, array{Weapon: \Smr\Combat\Weapon\AbstractWeapon, TargetPlanet: \Smr\Planet, Hit: bool, WeaponDamage?: WeaponDamageData, ActualDamage?: TakenDamageData, KillResults?: array{}}>, Drones?: array{Weapon: \Smr\Combat\Weapon\AbstractWeapon, TargetPlanet: \Smr\Planet, Hit: bool, WeaponDamage: WeaponDamageData, ActualDamage: TakenDamageData, KillResults?: array{}}}>}, Planet: array{Planet: \Smr\Planet, TotalDamage: int, TotalDamagePerTargetPlayer?: array<int, int>, DeadBeforeShot: bool, Weapons?: array<int, array{Weapon: \Smr\Combat\Weapon\AbstractWeapon, TargetPlayer: \Smr\AbstractPlayer, Hit: bool, WeaponDamage?: WeaponDamageData, ActualDamage?: TakenDamageData, KillResults?: array{DeadExp: int, LostCredits: int}}>, Drones?: array{Weapon: \Smr\Combat\Weapon\AbstractWeapon, TargetPlayer: \Smr\AbstractPlayer, Hit: bool, WeaponDamage: WeaponDamageData, ActualDamage: TakenDamageData, KillResults?: array{DeadExp: int, LostCredits: int}}}} $results
	 */
	public function __construct(
		private readonly int $sectorID,
		private readonly array $results,
		bool $playerDied
	) {
		// If the player died, make sure they see combat results
		$this->skipRedirect = $playerDied;
	}

	public function build(AbstractPlayer $player, Template $template): void {
		$template->assign('FullPlanetCombatResults', $this->results);
		$template->assign('MinimalDisplay', false);
		$template->assign('OverrideDeath', $player->isDead());
		$template->assign('Planet', Planet::getPlanet($player->getGameID(), $this->sectorID));
	}

}
