<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use AbstractSmrPlayer;
use Smr\Page\PlayerPage;
use Smr\Template;
use SmrForce;
use SmrPlayer;

class ForcesDrop extends PlayerPage {

	public string $file = 'forces_drop.php';

	public function __construct(
		private readonly ?int $ownerAccountID = null
	) {}

	public function build(AbstractSmrPlayer $player, Template $template): void {
		if ($this->ownerAccountID !== null) {
			$owner = SmrPlayer::getPlayer($this->ownerAccountID, $player->getGameID());
			$template->assign('PageTopic', 'Change ' . htmlentities($owner->getPlayerName()) . '\'s Forces');
			$owner_id = $this->ownerAccountID;
		} else {
			$template->assign('PageTopic', 'Drop Forces');
			$owner_id = $player->getAccountID();
		}

		$forces = SmrForce::getForce($player->getGameID(), $player->getSectorID(), $owner_id);

		$container = new ForcesDropProcessor($owner_id);

		$template->assign('Forces', $forces);
		$template->assign('SubmitHREF', $container->href());
	}

}
