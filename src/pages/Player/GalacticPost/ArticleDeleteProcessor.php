<?php declare(strict_types=1);

namespace Smr\Pages\Player\GalacticPost;

use Smr\Database;
use Smr\Page\PlayerPageProcessor;
use Smr\Player;
use Smr\Request;

class ArticleDeleteProcessor extends PlayerPageProcessor {

	public function __construct(
		private readonly int $articleID,
	) {}

	public function build(Player $player): never {
		$db = Database::getInstance();
		if (Request::getBool('action')) {
			$db->delete('galactic_post_article', [
				'game_id' => $player->getGameID(),
				'article_id' => $this->articleID,
			]);
		}

		$container = new ArticleView();
		$container->go();
	}

}
