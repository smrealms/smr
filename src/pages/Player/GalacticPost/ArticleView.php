<?php declare(strict_types=1);

namespace Smr\Pages\Player\GalacticPost;

use Smr\AbstractPlayer;
use Smr\Database;
use Smr\Menu;
use Smr\Page\PlayerPage;
use Smr\Player;
use Smr\Template;

class ArticleView extends PlayerPage {

	public string $file = 'galactic_post_view_article.php';

	public function __construct(
		private readonly ?int $articleID = null,
		private readonly bool $addedToNews = false
	) {}

	public function build(AbstractPlayer $player, Template $template): void {
		$db = Database::getInstance();

		$template->assign('PageTopic', 'Viewing Articles');
		Menu::galacticPost();

		// Get the articles that are not already in a paper
		$articles = [];
		$dbResult = $db->read('SELECT * FROM galactic_post_article WHERE article_id NOT IN (SELECT article_id FROM galactic_post_paper_content WHERE game_id = :game_id) AND game_id = :game_id', [
			'game_id' => $db->escapeNumber($player->getGameID()),
		]);
		foreach ($dbResult->records() as $dbRecord) {
			$title = $dbRecord->getString('title');
			$writer = Player::getPlayer($dbRecord->getInt('writer_id'), $player->getGameID());
			$container = new self($dbRecord->getInt('article_id'));
			$articles[] = [
				'title' => $title,
				'writer' => $writer->getDisplayName(),
				'link' => $container->href(),
			];
		}
		$template->assign('Articles', $articles);

		// Details about a selected article
		if ($this->articleID !== null) {
			$dbResult = $db->read('SELECT * FROM galactic_post_article WHERE game_id = :game_id AND article_id = :article_id', [
				'game_id' => $db->escapeNumber($player->getGameID()),
				'article_id' => $db->escapeNumber($this->articleID),
			]);
			$dbRecord = $dbResult->record();

			$container = new ArticleWrite($this->articleID);
			$editHREF = $container->href();

			$container = new ArticleDeleteConfirm($this->articleID);
			$deleteHREF = $container->href();

			$selectedArticle = [
				'title' => $dbRecord->getString('title'),
				'text' => $dbRecord->getString('text'),
				'editHREF' => $editHREF,
				'deleteHREF' => $deleteHREF,
			];
			$template->assign('SelectedArticle', $selectedArticle);

			$papers = [];
			$dbResult = $db->read('SELECT * FROM galactic_post_paper WHERE game_id = :game_id', [
				'game_id' => $db->escapeNumber($player->getGameID()),
			]);
			foreach ($dbResult->records() as $dbRecord) {
				$container = new ArticleAddToPaperProcessor($dbRecord->getInt('paper_id'), $this->articleID);
				$papers[] = [
					'title' => $dbRecord->getString('title'),
					'addHREF' => $container->href(),
				];
			}
			$template->assign('Papers', $papers);

			if (count($papers) === 0) {
				$container = new PaperMake();
				$template->assign('MakePaperHREF', $container->href());
			}

			// breaking news options
			$template->assign('AddedToNews', $this->addedToNews);
			if (!$this->addedToNews) {
				$container = new ArticleAddToNewsProcessor($this->articleID);
				$template->assign('AddToNewsHREF', $container->href());
			}
		}
	}

}
