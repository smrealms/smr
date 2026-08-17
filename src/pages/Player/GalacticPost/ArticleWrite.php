<?php declare(strict_types=1);

namespace Smr\Pages\Player\GalacticPost;

use Smr\Database;
use Smr\Menu;
use Smr\Page\PlayerPage;
use Smr\Player;
use Smr\Template;

class ArticleWrite extends PlayerPage {

	public function __construct(
		private readonly ?int $articleID = null,
		private readonly ?string $previewTitle = null,
		private readonly ?string $previewText = null,
	) {}

	public function build(Player $player, Template $template): void {
		Menu::galacticPost();

		$title = $this->previewTitle;
		$text = $this->previewText;

		if ($this->articleID !== null) {
			$template->pageTopic = 'Editing An Article';
			if ($this->previewText === null) {
				$db = Database::getInstance();
				$dbResult = $db->select(
					'galactic_post_article',
					[
						'game_id' => $player->getGameID(),
						'article_id' => $this->articleID,
					],
					['title', 'text'],
				);
				if ($dbResult->hasRecord()) {
					$dbRecord = $dbResult->record();
					$title = $dbRecord->getString('title');
					$text = $dbRecord->getString('text');
				}
			}
		} else {
			$template->pageTopic = 'Writing An Article';
		}

		$template->pageRenderer = fn() => ArticleWriteRenderer::render(
			PreviewTitle: $title,
			Preview: $text,
			SubmitArticlePage: new ArticleWriteProcessor($this->articleID),
		);
	}

}
