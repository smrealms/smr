<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use Smr\Alliance;
use Smr\Menu;
use Smr\Page\PlayerPage;
use Smr\Page\ReusableTrait;
use Smr\Pages\Shared\CommonMessageSendRenderer;
use Smr\Player;
use Smr\Template;

class AllianceBroadcast extends PlayerPage {

	use ReusableTrait;
	public function __construct(
		private readonly int $allianceID,
		private readonly ?string $preview = null,
	) {}

	public function build(Player $player, Template $template): void {
		$alliance = Alliance::getAlliance($this->allianceID, $player->getGameID());
		$template->pageTopic = $alliance->getAllianceDisplayName(false, true);
		Menu::alliance($alliance->getAllianceID());

		$template->pageRenderer = fn() => CommonMessageSendRenderer::render(
			Receiver: 'Whole Alliance',
			Preview: $this->preview,
			ThisPlayer: $player,
			MessageSendPage: new MessageSendProcessor(allianceID: $this->allianceID),
		);
	}

}
