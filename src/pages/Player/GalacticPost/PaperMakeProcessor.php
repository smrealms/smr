<?php declare(strict_types=1);

namespace Smr\Pages\Player\GalacticPost;

use Smr\Database;
use Smr\Page\PlayerPageProcessor;
use Smr\Player;
use Smr\Request;

class PaperMakeProcessor extends PlayerPageProcessor {

	public function build(Player $player): never {
		$db = Database::getInstance();
		$title = Request::get('title');
		$db->insert('galactic_post_paper', [
			'game_id' => $player->getGameID(),
			'title' => $title,
		]);
		//send em back
		$container = new ArticleView();
		$container->go();
	}

}
