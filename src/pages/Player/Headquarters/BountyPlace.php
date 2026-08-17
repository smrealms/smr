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
		$dbResult = $db->read('SELECT player_id, player_name FROM player JOIN account USING(account_id) WHERE game_id = :game_id AND account_id != :account_id ORDER BY player_name', $player->SQLID);
		foreach ($dbResult->records() as $dbRecord) {
			$bountyPlayers[$dbRecord->getInt('player_id')] = htmlentities($dbRecord->getString('player_name'));
		}
		$template->pageRenderer = fn() => BountyPlaceRenderer::render(
			SubmitHREF: new BountyPlaceProcessor($this->locationID)->href(),
			BountyPlayers: $bountyPlayers,
		);
	}

}
