<?php declare(strict_types=1);

namespace Smr\Pages\Player\GalacticPost;

use AbstractSmrPlayer;
use Smr\Database;
use Smr\Page\PlayerPageProcessor;
use Smr\Request;

class ArticleDeleteProcessor extends PlayerPageProcessor {

	public function __construct(
		private readonly int $articleID
	) {}

	public function build(AbstractSmrPlayer $player): never {
		$db = Database::getInstance();
		if (Request::get('action') == 'Yes') {
			$db->write('DELETE FROM galactic_post_article WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . ' AND article_id = ' . $db->escapeNumber($this->articleID));
		}

		$container = new ArticleView();
		$container->go();
	}

}
