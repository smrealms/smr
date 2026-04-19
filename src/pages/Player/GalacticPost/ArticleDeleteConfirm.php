<?php declare(strict_types=1);

namespace Smr\Pages\Player\GalacticPost;

use Smr\Database;
use Smr\Page\PlayerPage;
use Smr\Player;
use Smr\Template;

class ArticleDeleteConfirm extends PlayerPage {

	public string $file = 'galactic_post_article_delete_confirm.php';

	public function __construct(
		private readonly int $articleID,
	) {}

	public function build(Player $player, Template $template): void {
		$db = Database::getInstance();

		$template->assign('PageTopic', 'Delete Article - Confirm');
		$dbResult = $db->select(
			'galactic_post_article',
			['article_id' => $this->articleID, 'game_id' => $player->getGameID()],
			['title'],
		);
		$template->assign('ArticleTitle', $dbResult->record()->getString('title'));

		$container = new ArticleDeleteProcessor($this->articleID);
		$template->assign('SubmitHREF', $container->href());
	}

}
