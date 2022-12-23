<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use AbstractSmrPlayer;
use Menu;
use Smr\Database;
use Smr\Page\PlayerPage;
use Smr\Template;

class MessageBlacklist extends PlayerPage {

	public string $file = 'message_blacklist.php';

	public function __construct(
		private readonly ?string $message = null
	) {}

	public function build(AbstractSmrPlayer $player, Template $template): void {
		$template->assign('PageTopic', 'Player Blacklist');

		Menu::messages();

		if ($this->message !== null) {
			$template->assign('Message', $this->message);
		}

		$db = Database::getInstance();
		$dbResult = $db->read('SELECT p.player_name, p.game_id, b.entry_id FROM player p JOIN message_blacklist b ON p.account_id = b.blacklisted_id AND b.game_id = p.game_id WHERE b.account_id=' . $db->escapeNumber($player->getAccountID()) . ' ORDER BY p.game_id, p.player_name');

		$blacklist = [];
		foreach ($dbResult->records() as $dbRecord) {
			$blacklist[] = $dbRecord->getRow();
		}
		$template->assign('Blacklist', $blacklist);

		if ($blacklist) {
			$container = new MessageBlacklistDeleteProcessor();
			$template->assign('BlacklistDeleteHREF', $container->href());
		}

		$container = new MessageBlacklistAddProcessor();
		$template->assign('BlacklistAddHREF', $container->href());
	}

}
