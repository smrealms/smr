<?php declare(strict_types=1);

namespace Smr\Pages\Player\GalacticPost;

use Smr\AbstractPlayer;
use Smr\Database;
use Smr\Page\PlayerPage;
use Smr\Template;

class PaperDeleteConfirm extends PlayerPage {

	public string $file = 'galactic_post_paper_delete_confirm.php';

	public function __construct(
		private readonly int $paperID,
	) {}

	public function build(AbstractPlayer $player, Template $template): void {
		$db = Database::getInstance();

		$template->assign('PageTopic', 'Delete Paper - Confirm');
		$dbResult = $db->read('SELECT title FROM galactic_post_paper WHERE game_id = :game_id AND paper_id = :paper_id', [
			'game_id' => $db->escapeNumber($player->getGameID()),
			'paper_id' => $db->escapeNumber($this->paperID),
		]);
		$template->assign('PaperTitle', $dbResult->record()->getString('title'));

		$articles = [];
		$dbResult = $db->read('SELECT title FROM galactic_post_paper_content JOIN galactic_post_article USING (game_id, article_id) WHERE game_id = :game_id AND paper_id = :paper_id', [
			'game_id' => $db->escapeNumber($player->getGameID()),
			'paper_id' => $db->escapeNumber($this->paperID),
		]);
		foreach ($dbResult->records() as $dbRecord) {
			$articles[] = bbify($dbRecord->getString('title'));
		}
		$template->assign('Articles', $articles);

		$container = new PaperDeleteProcessor($this->paperID);
		$template->assign('SubmitHREF', $container->href());
	}

}
