<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use Smr\AbstractPlayer;
use Smr\Page\PlayerPage;
use Smr\Player;
use Smr\Template;

class AttackPlayer extends PlayerPage {

	public string $file = 'trader_attack.php';

	/**
	 * @param array{Attackers: array{TotalDamage: int, Traders: array<int, array{Player: \Smr\AbstractPlayer, TotalDamage: int, TotalDamagePerTargetPlayer?: array<int, int>, DeadBeforeShot: bool, Weapons: array<int, array{Weapon: \Smr\Combat\Weapon\AbstractWeapon, TargetPlayer: \Smr\AbstractPlayer, Hit: bool, WeaponDamage?: WeaponDamageData, ActualDamage?: TakenDamageData, KillResults?: array{DeadExp: int, KillerExp: int, KillerCredits: int}}>, Drones?: array{Weapon: \Smr\Combat\Weapon\AbstractWeapon, TargetPlayer: \Smr\AbstractPlayer, Hit: bool, WeaponDamage: WeaponDamageData, ActualDamage: TakenDamageData, KillResults?: array{DeadExp: int, KillerExp: int, KillerCredits: int}}}>}, Defenders: array{TotalDamage: int, Traders: array<int, array{Player: \Smr\AbstractPlayer, TotalDamage: int, TotalDamagePerTargetPlayer?: array<int, int>, DeadBeforeShot: bool, Weapons: array<int, array{Weapon: \Smr\Combat\Weapon\AbstractWeapon, TargetPlayer: \Smr\AbstractPlayer, Hit: bool, WeaponDamage?: WeaponDamageData, ActualDamage?: TakenDamageData, KillResults?: array{DeadExp: int, KillerExp: int, KillerCredits: int}}>, Drones?: array{Weapon: \Smr\Combat\Weapon\AbstractWeapon, TargetPlayer: \Smr\AbstractPlayer, Hit: bool, WeaponDamage: WeaponDamageData, ActualDamage: TakenDamageData, KillResults?: array{DeadExp: int, KillerExp: int, KillerCredits: int}}}>}} $results
	 */
	public function __construct(
		private readonly array $results,
		private readonly ?int $targetAccountID,
		bool $playerDied
	) {
		$this->skipRedirect = $playerDied;
	}

	public function build(AbstractPlayer $player, Template $template): void {
		$template->assign('TraderCombatResults', $this->results);
		$template->assign('MinimalDisplay', false);
		if ($this->targetAccountID !== null) {
			$template->assign('Target', Player::getPlayer($this->targetAccountID, $player->getGameID()));
		}
		$template->assign('OverrideDeath', $player->isDead());
	}

}
