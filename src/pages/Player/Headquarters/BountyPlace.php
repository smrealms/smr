<?php declare(strict_types=1);

namespace Smr\Pages\Player\Headquarters;

use Smr\Database;
use Smr\Menu;
use Smr\Page\PlayerPage;
use Smr\Player;
use Smr\Template;

class BountyPlace extends PlayerPage {

	public function __construct(
		private readonly int $locationID,
	) {}

	public function build(Player $player, Template $template): void {
		$template->pageTopic = 'Place Bounty';

		Menu::headquarters($this->locationID);

		$bountyPlayers = [];
		$db = Database::getInstance();
		$dbResult = $db->read('SELECT player_number, player_name FROM player WHERE game_id = :game_id AND player_id != :player_id ORDER BY player_name', [
			...$player->SQLID,
			'game_id' => $db->escapeNumber($player->getGameID()),
		]);
		foreach ($dbResult->records() as $dbRecord) {
			$bountyPlayers[$dbRecord->getInt('player_number')] = htmlentities($dbRecord->getString('player_name'));
		}
		$template->pageRenderer = fn() => BountyPlaceRenderer::render(
			SubmitHREF: new BountyPlaceProcessor($this->locationID)->href(),
			BountyPlayers: $bountyPlayers,
		);
	}

}
