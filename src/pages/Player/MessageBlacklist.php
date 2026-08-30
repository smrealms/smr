<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use Smr\Database;
use Smr\Menu;
use Smr\Page\PlayerPage;
use Smr\Player;
use Smr\Template;

class MessageBlacklist extends PlayerPage {

	public function __construct(
		private readonly ?string $message = null,
	) {}

	public function build(Player $player, Template $template): void {
		$template->pageTopic = 'Player Blacklist';

		Menu::messages();

		$db = Database::getInstance();
		$dbResult = $db->read('SELECT p.player_name, p.game_id, b.entry_id FROM player p JOIN message_blacklist b ON p.player_id = b.blacklisted_player_id WHERE b.player_id = :player_id ORDER BY p.game_id, p.player_name', [
			'player_id' => $db->escapeNumber($player->getPlayerID()),
		]);

		$blacklist = [];
		foreach ($dbResult->records() as $dbRecord) {
			$blacklist[] = $dbRecord->getRow();
		}
		$template->pageRenderer = fn() => MessageBlacklistRenderer::render(
			Message: $this->message,
			Blacklist: $blacklist,
			BlacklistDeleteHREF: new MessageBlacklistDeleteProcessor()->href(),
			BlacklistAddHREF: new MessageBlacklistAddProcessor()->href(),
		);
	}

}
