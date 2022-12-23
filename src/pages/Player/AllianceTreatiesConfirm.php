<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use AbstractSmrPlayer;
use Menu;
use Smr\Database;
use Smr\Page\PlayerPage;
use Smr\Request;
use Smr\Template;
use SmrAlliance;
use SmrTreaty;

class AllianceTreatiesConfirm extends PlayerPage {

	public string $file = 'alliance_treaties_confirm.php';

	public function build(AbstractSmrPlayer $player, Template $template): void {
		$alliance_id_1 = $player->getAllianceID();
		$alliance_id_2 = Request::getInt('proposedAlliance');

		$db = Database::getInstance();
		$dbResult = $db->read('SELECT 1 FROM alliance_treaties WHERE (alliance_id_1 = ' . $db->escapeNumber($alliance_id_1) . ' OR alliance_id_1 = ' . $alliance_id_2 . ') AND (alliance_id_2 = ' . $db->escapeNumber($alliance_id_1) . ' OR alliance_id_2 = ' . $db->escapeNumber($alliance_id_2) . ') AND game_id = ' . $db->escapeNumber($player->getGameID()));
		if ($dbResult->hasRecord()) {
			$message = '<span class="red bold">ERROR:</span> There is already an outstanding treaty with that alliance.';
			$container = new AllianceTreaties($message);
			$container->go();
		}

		$alliance1 = SmrAlliance::getAlliance($alliance_id_1, $player->getGameID());
		$alliance2 = SmrAlliance::getAlliance($alliance_id_2, $player->getGameID());
		$template->assign('AllianceName', $alliance2->getAllianceDisplayName());

		$template->assign('PageTopic', 'Alliance Treaty Confirmation');
		Menu::alliance($alliance1->getAllianceID());

		// Get the terms selected for this offer
		$terms = [];
		foreach (array_keys(SmrTreaty::TYPES) as $type) {
			$terms[$type] = Request::has($type);
		}
		// A few terms get added automatically if a more restrictive term has
		// been selected.
		$terms['trader_nap'] = $terms['trader_nap'] || $terms['trader_defend'] || $terms['trader_assist'];
		$terms['planet_land'] = $terms['planet_land'] || $terms['planet_nap'];
		$terms['mb_read'] = $terms['mb_read'] || $terms['mb_write'];
		$template->assign('Terms', $terms);

		// Create links for yes/no response
		$container = new AllianceTreatiesConfirmProcessor($alliance_id_2, $terms);
		$template->assign('YesHREF', $container->href());

		$container = new AllianceTreaties();
		$template->assign('NoHREF', $container->href());
	}

}
