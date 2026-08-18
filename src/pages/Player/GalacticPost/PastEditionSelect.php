<?php declare(strict_types=1);

namespace Smr\Pages\Player\GalacticPost;

use Smr\Database;
use Smr\Menu;
use Smr\Page\PlayerPage;
use Smr\Player;
use Smr\Template;

class PastEditionSelect extends PlayerPage {

	public function __construct(
		private readonly int $gameID,
	) {}

	public function build(Player $player, Template $template): void {
		$template->pageTopic = 'Past <i>Galactic Post</i> Editions';
		Menu::galacticPost();

		// Get the list of games with published papers
		// Add the current game to this list no matter what
		$db = Database::getInstance();
		$dbResult = $db->read('SELECT game_name, game_id FROM game WHERE game_id IN (SELECT DISTINCT game_id FROM galactic_post_paper WHERE online_since IS NOT NULL) OR game_id = :game_id ORDER BY game_id DESC', [
			'game_id' => $db->escapeNumber($player->getGameID()),
		]);
		$publishedGames = [];
		foreach ($dbResult->records() as $dbRecord) {
			$publishedGames[] = [
				'game_name' => $dbRecord->getString('game_name'),
				'game_id' => $dbRecord->getInt('game_id'),
			];
		}
		// Get the list of published papers for the selected game
		$dbResult = $db->read('SELECT * FROM galactic_post_paper WHERE online_since IS NOT NULL AND game_id = :game_id', [
			'game_id' => $db->escapeNumber($this->gameID),
		]);
		$pastEditions = [];
		foreach ($dbResult->records() as $dbRecord) {
			$container = new EditionRead($this->gameID, $dbRecord->getInt('paper_id'), true);

			$pastEditions[] = [
				'title' => $dbRecord->getString('title'),
				'online_since' => $dbRecord->getInt('online_since'),
				'href' => $container->href(),
			];
		}
		$template->pageRenderer = fn() => PastEditionSelectRenderer::render(
			SelectedGame: $this->gameID,
			SelectGameHREF: new PastEditionSelectProcessor()->href(),
			PublishedGames: $publishedGames,
			PastEditions: $pastEditions,
		);
	}

}
