<?php declare(strict_types=1);

namespace Smr\Pages\Player\GalacticPost;

use Smr\AbstractPlayer;
use Smr\Database;
use Smr\Menu;
use Smr\Page\PlayerPage;
use Smr\Template;

class EditionRead extends PlayerPage {

	public string $file = 'galactic_post_read.php';

	public function __construct(
		private readonly int $gameID,
		private readonly ?int $paperID,
		private readonly bool $showBackButton = false
	) {}

	public function build(AbstractPlayer $player, Template $template): void {
		Menu::galacticPost();

		if ($this->paperID !== null) {
			$template->assign('PaperGameID', $this->gameID);

			// Create link back to past editions
			if ($this->showBackButton) {
				$container = new PastEditionSelect($this->gameID);
				$template->assign('BackHREF', $container->href());
			}

			$db = Database::getInstance();
			$dbResult = $db->read('SELECT title FROM galactic_post_paper WHERE game_id = ' . $db->escapeNumber($this->gameID) . ' AND paper_id = ' . $this->paperID);
			$paper_name = bbifyMessage($dbResult->record()->getString('title'), $this->gameID);
			$template->assign('PageTopic', 'Reading <i>Galactic Post</i> Edition : ' . $paper_name);

			//now get the articles in this paper.
			$dbResult = $db->read('SELECT * FROM galactic_post_paper_content JOIN galactic_post_article USING(game_id, article_id) WHERE paper_id = ' . $db->escapeNumber($this->paperID) . ' AND game_id = ' . $db->escapeNumber($this->gameID));

			$articles = [];
			foreach ($dbResult->records() as $dbRecord) {
				$articles[] = [
					'title' => $dbRecord->getString('title'),
					'text' => $dbRecord->getString('text'),
				];
			}

			// Determine the layout of the articles on the page
			$articleLayout = [];
			$row = 0;
			foreach ($articles as $i => $article) {
				$articleLayout[$row][] = $article;

				// start a new row every 2 articles
				if ($i % 2 == 1) {
					$row++;
				}
			}
			$template->assign('ArticleLayout', $articleLayout);
		} else {
			$template->assign('PageTopic', 'Galactic Post');
		}
	}

}
