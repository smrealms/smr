<?php declare(strict_types=1);

namespace Smr\Pages\Player\GalacticPost;

use Smr\Database;
use Smr\Page\PlayerPage;
use Smr\Player;
use Smr\Template;

class ArticleDeleteConfirm extends PlayerPage {

	public function __construct(
		private readonly int $articleID,
	) {}

	public function build(Player $player, Template $template): void {
		$db = Database::getInstance();

		$template->pageTopic = 'Delete Article - Confirm';
		$dbResult = $db->select(
			'galactic_post_article',
			['article_id' => $this->articleID, 'game_id' => $player->getGameID()],
			['title'],
		);
		$template->pageRenderer = fn() => ArticleDeleteConfirmRenderer::render(
			ConfirmHREF: new ArticleDeleteProcessor($this->articleID)->href(),
			CancelHREF: new ArticleView($this->articleID)->href(),
			ArticleTitle: $dbResult->record()->getString('title'),
		);
	}

}
