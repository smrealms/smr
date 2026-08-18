<?php declare(strict_types=1);

namespace Smr\Pages\Player\GalacticPost;

use Smr\Database;
use Smr\Menu;
use Smr\Page\PlayerPage;
use Smr\Player;
use Smr\Template;

class ArticleView extends PlayerPage {

	public function __construct(
		private readonly ?int $articleID = null,
		private readonly bool $addedToNews = false,
	) {}

	public function build(Player $player, Template $template): void {
		$db = Database::getInstance();

		$template->pageTopic = 'Viewing Articles';
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

		// Details about a selected article
		if ($this->articleID !== null) {
			$dbResult = $db->select('galactic_post_article', [
				'game_id' => $player->getGameID(),
				'article_id' => $this->articleID,
			]);
			$dbRecord = $dbResult->record();

			$selectedArticle = [
				'title' => $dbRecord->getString('title'),
				'text' => $dbRecord->getString('text'),
				'editHREF' => new ArticleWrite($this->articleID)->href(),
				'deleteHREF' => new ArticleDeleteConfirm($this->articleID)->href(),
				'makePaperHREF' => new PaperMake()->href(),
				'addToNewsHREF' => $this->addedToNews ? null : new ArticleAddToNewsProcessor($this->articleID)->href(),
			];

			$papers = [];
			$dbResult = $db->select('galactic_post_paper', [
				'game_id' => $player->getGameID(),
			]);
			foreach ($dbResult->records() as $dbRecord) {
				$container = new ArticleAddToPaperProcessor($dbRecord->getInt('paper_id'), $this->articleID);
				$papers[] = [
					'title' => $dbRecord->getString('title'),
					'addToPaperHREF' => $container->href(),
				];
			}
			$selectedArticle['papers'] = $papers;
		} else {
			$selectedArticle = null;
		}

		$template->pageRenderer = fn() => ArticleViewRenderer::render(
			Articles: $articles,
			SelectedArticle: $selectedArticle,
		);
	}

}
