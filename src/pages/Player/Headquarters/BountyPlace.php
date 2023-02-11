<?php declare(strict_types=1);

namespace Smr\Pages\Player\Headquarters;

use Smr\AbstractPlayer;
use Smr\Database;
use Smr\Menu;
use Smr\Page\PlayerPage;
use Smr\Template;

class BountyPlace extends PlayerPage {

	public string $file = 'bounty_place.php';

	public function __construct(
		private readonly int $locationID
	) {}

	public function build(AbstractPlayer $player, Template $template): void {
		$template->assign('PageTopic', 'Place Bounty');

		Menu::headquarters($this->locationID);

		$container = new BountyPlaceProcessor($this->locationID);
		$template->assign('SubmitHREF', $container->href());

		$bountyPlayers = [];
		$db = Database::getInstance();
		$dbResult = $db->read('SELECT player_id, player_name FROM player JOIN account USING(account_id) WHERE game_id = :game_id AND account_id != :account_id ORDER BY player_name', [
			'game_id' => $db->escapeNumber($player->getGameID()),
			'account_id' => $db->escapeNumber($player->getAccountID()),
		]);
		foreach ($dbResult->records() as $dbRecord) {
			$bountyPlayers[$dbRecord->getInt('player_id')] = htmlentities($dbRecord->getString('player_name'));
		}
		$template->assign('BountyPlayers', $bountyPlayers);
	}

}
