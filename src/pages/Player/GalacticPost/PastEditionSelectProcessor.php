<?php declare(strict_types=1);

namespace Smr\Pages\Player\GalacticPost;

use AbstractSmrPlayer;
use Smr\Page\PlayerPageProcessor;
use Smr\Request;

class PastEditionSelectProcessor extends PlayerPageProcessor {

	public function build(AbstractSmrPlayer $player): never {
		$selectedGameID = Request::getInt('selected_game_id');
		(new PastEditionSelect($selectedGameID))->go();
	}

}
