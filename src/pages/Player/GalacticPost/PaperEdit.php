<?php declare(strict_types=1);

namespace Smr\Pages\Player\GalacticPost;

use Smr\Database;
use Smr\Menu;
use Smr\Page\PlayerPage;
use Smr\Player;
use Smr\Template;

class PaperEdit extends PlayerPage {

	public string $file = 'galactic_post_paper_edit.php';

	public function __construct(
		private readonly int $paperID,
	) {}

	public function build(Player $player, Template $template): void {
		$template->assign('PageTopic', 'Edit Paper');
		Menu::galacticPost();

		$db = Database::getInstance();
		$sqlParams = [
			'paper_id' => $this->paperID,
			'game_id' => $player->getGameID(),
		];
		$dbResult = $db->select('galactic_post_paper', $sqlParams, ['title']);
		$template->assign('PaperTitle', bbify($dbResult->record()->getString('title')));

		$dbResult = $db->read('SELECT * FROM galactic_post_paper_content JOIN galactic_post_article USING (game_id, article_id) WHERE paper_id = :paper_id AND game_id = :game_id', $sqlParams);

		$articles = [];
		foreach ($dbResult->records() as $dbRecord) {
			$container = new PaperEditProcessor($this->paperID, $dbRecord->getInt('article_id'));
			$articles[] = [
				'title' => bbify($dbRecord->getString('title')),
				'text' => bbify($dbRecord->getString('text')),
				'editHREF' => $container->href(),
			];
		}
		$template->assign('Articles', $articles);
	}

}
