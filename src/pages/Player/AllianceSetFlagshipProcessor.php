<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use AbstractSmrPlayer;
use Smr\Page\PlayerPageProcessor;
use Smr\Request;

class AllianceSetFlagshipProcessor extends PlayerPageProcessor {

	public function build(AbstractSmrPlayer $player): never {
		$alliance = $player->getAlliance();

		$flagshipID = Request::getInt('flagship_id');

		$alliance->setFlagshipID($flagshipID);
		$alliance->update();

		(new AllianceSetOp())->go();
	}

}
