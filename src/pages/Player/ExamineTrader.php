<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use AbstractSmrPlayer;
use Smr\Page\PlayerPage;
use Smr\Template;
use SmrPlayer;

class ExamineTrader extends PlayerPage {

	public string $file = 'trader_examine.php';

	public function __construct(
		private readonly int $targetAccountID
	) {}

	public function build(AbstractSmrPlayer $player, Template $template): void {
		// Get the player we're attacking
		$targetPlayer = SmrPlayer::getPlayer($this->targetAccountID, $player->getGameID());

		if ($targetPlayer->isDead()) {
			$msg = '<span class="red bold">ERROR:</span> Target already dead.';
			$container = new CurrentSector(message: $msg);
			$container->go();
		}

		$template->assign('PageTopic', 'Examine Ship');
		$template->assign('TargetPlayer', $targetPlayer);
	}

}
