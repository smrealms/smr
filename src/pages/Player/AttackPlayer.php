<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use AbstractSmrPlayer;
use Smr\Page\PlayerPage;
use Smr\Template;
use SmrPlayer;

class AttackPlayer extends PlayerPage {

	public string $file = 'trader_attack.php';

	/**
	 * @param array<mixed> $results
	 */
	public function __construct(
		private readonly array $results,
		private readonly ?int $targetAccountID,
		bool $playerDied
	) {
		$this->skipRedirect = $playerDied;
	}

	public function build(AbstractSmrPlayer $player, Template $template): void {
		$template->assign('TraderCombatResults', $this->results);
		$template->assign('MinimalDisplay', false);
		if ($this->targetAccountID !== null) {
			$template->assign('Target', SmrPlayer::getPlayer($this->targetAccountID, $player->getGameID()));
		}
		$template->assign('OverrideDeath', $player->isDead());
	}

}
