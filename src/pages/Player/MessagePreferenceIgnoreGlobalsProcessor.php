<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use Smr\Html\Submit;
use Smr\Page\PlayerPageProcessor;
use Smr\Player;
use Smr\Request;

class MessagePreferenceIgnoreGlobalsProcessor extends PlayerPageProcessor {

	private const string ACTION = 'action';

	public readonly Submit $actionYes;
	public readonly Submit $actionNo;

	public function __construct(
		private readonly int $folderID,
	) {
		$this->actionYes = new Submit(self::ACTION, 'Yes');
		$this->actionNo = new Submit(self::ACTION, 'No');
	}

	public function build(Player $player): never {
		$player->setIgnoreGlobals(Request::getBool(self::ACTION));

		$container = new MessageView($this->folderID);
		$container->go();
	}

}
