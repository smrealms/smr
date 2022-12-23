<?php declare(strict_types=1);

namespace Smr\Pages\Player\GalacticPost;

use AbstractSmrPlayer;
use Menu;
use Smr\Database;
use Smr\Page\PlayerPage;
use Smr\Template;

class PastEditionSelect extends PlayerPage {

	public string $file = 'galactic_post_past.php';

	public function __construct(
		private readonly int $gameID
	) {}

	public function build(AbstractSmrPlayer $player, Template $template): void {
		$template->assign('PageTopic', 'Past <i>Galactic Post</i> Editions');
		Menu::galacticPost();

		$container = new PastEditionSelectProcessor();
		$template->assign('SelectGameHREF', $container->href());

		// View past editions of current game by default
		$template->assign('SelectedGame', $this->gameID);

		// Get the list of games with published papers
		// Add the current game to this list no matter what
		$db = Database::getInstance();
		$dbResult = $db->read('SELECT game_name, game_id FROM game WHERE game_id IN (SELECT DISTINCT game_id FROM galactic_post_paper WHERE online_since IS NOT NULL) OR game_id=' . $db->escapeNumber($player->getGameID()) . ' ORDER BY game_id DESC');
		$publishedGames = [];
		foreach ($dbResult->records() as $dbRecord) {
			$publishedGames[] = [
				'game_name' => $dbRecord->getString('game_name'),
				'game_id' => $dbRecord->getInt('game_id'),
			];
		}
		$template->assign('PublishedGames', $publishedGames);

		// Get the list of published papers for the selected game
		$dbResult = $db->read('SELECT * FROM galactic_post_paper WHERE online_since IS NOT NULL AND game_id=' . $db->escapeNumber($this->gameID));
		$pastEditions = [];
		foreach ($dbResult->records() as $dbRecord) {
			$container = new EditionRead($this->gameID, $dbRecord->getInt('paper_id'), true);

			$pastEditions[] = [
				'title' => $dbRecord->getString('title'),
				'online_since' => $dbRecord->getInt('online_since'),
				'href' => $container->href(),
			];
		}
		$template->assign('PastEditions', $pastEditions);
	}

}
