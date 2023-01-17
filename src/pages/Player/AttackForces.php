<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use Smr\AbstractPlayer;
use Smr\Force;
use Smr\Page\PlayerPage;
use Smr\Template;

class AttackForces extends PlayerPage {

	public string $file = 'forces_attack.php';

	/**
	 * @param array{Attackers: array{TotalDamage: int, Traders?: array<int, array{Player: \Smr\AbstractPlayer, TotalDamage: int, DeadBeforeShot: bool, Weapons: array<int, array{Weapon: \Smr\Combat\Weapon\AbstractWeapon, TargetForces: \Smr\Force, Hit: bool, WeaponDamage?: WeaponDamageData, ActualDamage?: ForceTakenDamageData, KillResults?: array{}}>, Drones?: array{Weapon: \Smr\Combat\Weapon\AbstractWeapon, TargetForces: \Smr\Force, Hit: bool, WeaponDamage: WeaponDamageData, ActualDamage: ForceTakenDamageData, KillResults?: array{}}}>}, Forces?: array{TotalDamage: int, DeadBeforeShot: bool, ForcesDestroyed?: bool, Mines?: array{Weapon: \Smr\Combat\Weapon\AbstractWeapon, TargetPlayer: \Smr\AbstractPlayer, Hit: bool, WeaponDamage: WeaponDamageData, ActualDamage: TakenDamageData, KillResults?: array{DeadExp: int, LostCredits: int}}, Drones?: array{Weapon: \Smr\Combat\Weapon\AbstractWeapon, TargetPlayer: \Smr\AbstractPlayer, Hit: bool, WeaponDamage: WeaponDamageData, ActualDamage: TakenDamageData, KillResults?: array{DeadExp: int, LostCredits: int}}, Scouts?: array{Weapon: \Smr\Combat\Weapon\AbstractWeapon, TargetPlayer: \Smr\AbstractPlayer, Hit: bool, WeaponDamage: WeaponDamageData, ActualDamage: TakenDamageData, KillResults?: array{DeadExp: int, LostCredits: int}}}, Forced: bool} $results
	 */
	public function __construct(
		private readonly int $ownerAccountID,
		private readonly array $results,
		bool $playerDied
	) {
		// If the player died, make sure they see combat results
		$this->skipRedirect = $playerDied;
	}

	public function build(AbstractPlayer $player, Template $template): void {
		$template->assign('FullForceCombatResults', $this->results);

		if ($this->ownerAccountID > 0) {
			$template->assign('Target', Force::getForce($player->getGameID(), $player->getSectorID(), $this->ownerAccountID));
		}

		$template->assign('OverrideDeath', $player->isDead());
	}

}
