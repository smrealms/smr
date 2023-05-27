<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use Smr\AbstractPlayer;
use Smr\Page\PlayerPageProcessor;
use Smr\Request;
use Smr\ScoutMessageGroupType;

class MessagePreferenceProcessor extends PlayerPageProcessor {

	public function __construct(
		private readonly int $folderID,
	) {}

	public function build(AbstractPlayer $player): never {
		if (Request::has('ignore_globals')) {
			$player->setIgnoreGlobals(Request::getBool('ignore_globals'));
		} elseif (Request::has('group_scouts')) {
			$groupType = ScoutMessageGroupType::from(Request::get('group_scouts'));
			$player->setScoutMessageGroupType($groupType);
		}

		$container = new MessageView($this->folderID);
		$container->go();
	}

}
