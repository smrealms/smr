<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use Smr\AbstractPlayer;
use Smr\Page\PlayerPage;
use Smr\Template;

class AttackPort extends PlayerPage {

	public string $file = 'port_attack.php';

	/**
	 * @param ?array{Attackers: array{TotalDamage: int, Downgrades: int, Traders?: array<int, array{Player: \Smr\AbstractPlayer, TotalDamage: int, DeadBeforeShot: bool, Weapons: array<int, array{Weapon: \Smr\Combat\Weapon\AbstractWeapon, TargetPort: \Smr\Port, Hit: bool, WeaponDamage?: WeaponDamageData, ActualDamage?: TakenDamageData, KillResults?: array{}}>, Drones?: array{Weapon: \Smr\Combat\Weapon\AbstractWeapon, TargetPort: \Smr\Port, Hit: bool, WeaponDamage: WeaponDamageData, ActualDamage: TakenDamageData, KillResults?: array{}}}>}, Port: array{Port: \Smr\Port, TotalDamage: int, TotalDamagePerTargetPlayer: array<int, int>, TotalShotsPerTargetPlayer: array<int, int>, DeadBeforeShot: bool, Weapons?: array<int, array{Weapon: \Smr\Combat\Weapon\AbstractWeapon, TargetPlayer: \Smr\AbstractPlayer, Hit: bool, WeaponDamage?: WeaponDamageData, ActualDamage?: TakenDamageData, KillResults?: array{DeadExp: int, LostCredits: int}}>, Drones?: array{Weapon: \Smr\Combat\Weapon\AbstractWeapon, TargetPlayer: \Smr\AbstractPlayer, Hit: bool, WeaponDamage: WeaponDamageData, ActualDamage: TakenDamageData, KillResults?: array{DeadExp: int, LostCredits: int}}}} $results
	 */
	public function __construct(
		private readonly ?array $results = null,
		bool $playerDied = false
	) {
		// If the player died, make sure they see combat results
		$this->skipRedirect = $playerDied;
	}

	public function build(AbstractPlayer $player, Template $template): void {
		$port = $player->getSector()->getPort();

		if ($this->results !== null) {
			$template->assign('FullPortCombatResults', $this->results);
			$template->assign('AlreadyDestroyed', false);
			$template->assign('CreditedAttacker', true);
		} else {
			$template->assign('AlreadyDestroyed', true);
			$template->assign('CreditedAttacker', $port->isCreditedAttacker($player));
		}
		$template->assign('MinimalDisplay', false);

		$template->assign('OverrideDeath', $player->isDead());
		$template->assign('Port', $port);
	}

}
