<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use Smr\Html\Submit;
use Smr\Page\PlayerPageProcessor;
use Smr\Player;
use Smr\Request;
use Smr\ScoutMessageGroupType;

class MessagePreferenceScoutGroupProcessor extends PlayerPageProcessor {

	private const string ACTION = 'action';

	/** @var array<string, Submit> */
	public readonly array $actionScoutGroup;

	public function __construct(
		private readonly int $folderID,
	) {
		$groupScouts = [];
		foreach (ScoutMessageGroupType::cases() as $groupType) {
			$groupScouts[$groupType->value] = new Submit(self::ACTION, $groupType->value);
		}
		$this->actionScoutGroup = $groupScouts;
	}

	public function build(Player $player): never {
		$groupType = ScoutMessageGroupType::from(Request::get(self::ACTION));
		$player->setScoutMessageGroupType($groupType);

		$container = new MessageView($this->folderID);
		$container->go();
	}

}
