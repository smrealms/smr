<?php declare(strict_types=1);

namespace Smr\Pages\Player\GalacticPost;

use Smr\Database;
use Smr\Menu;
use Smr\Page\PlayerPage;
use Smr\Player;
use Smr\Template;

class ArticleWrite extends PlayerPage {

	public string $file = 'galactic_post_write_article.php';

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
			$template->assign('PageTopic', 'Editing An Article');
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
			$template->assign('PageTopic', 'Writing An Article');
		}

		$template->assign('PreviewTitle', $title);
		$template->assign('Preview', $text);

		$container = new ArticleWriteProcessor($this->articleID);
		$template->assign('SubmitArticleHref', $container->href());
	}

}
