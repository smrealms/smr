<?php declare(strict_types=1);

namespace Smr\Pages\Player\GalacticPost;

use Smr\AbstractPlayer;
use Smr\Database;
use Smr\Page\PlayerPageProcessor;
use Smr\Request;

class PaperMakeProcessor extends PlayerPageProcessor {

	public function build(AbstractPlayer $player): never {
		$db = Database::getInstance();
		$dbResult = $db->select(
			'galactic_post_paper',
			['game_id' => $player->getGameID()],
			orderBy: ['paper_id'],
			order: ['DESC'],
		);
		if ($dbResult->hasRecord()) {
			$num = $dbResult->record()->getInt('paper_id') + 1;
		} else {
			$num = 1;
		}
		$title = Request::get('title');
		$db->insert('galactic_post_paper', [
			'game_id' => $player->getGameID(),
			'paper_id' => $num,
			'title' => $title,
		]);
		//send em back
		$container = new ArticleView();
		$container->go();
	}

}
