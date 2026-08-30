<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use Smr\Menu;
use Smr\Page\PlayerPage;
use Smr\Page\ReusableTrait;
use Smr\Pages\Shared\CommonMessageSendRenderer;
use Smr\Player;
use Smr\Template;

class MessageSend extends PlayerPage {

	use ReusableTrait;
	public function __construct(
		private readonly ?int $receiverPlayerID = null,
		private readonly ?string $preview = null,
	) {}

	public function build(Player $player, Template $template): void {
		$template->pageTopic = 'Send Message';

		Menu::messages();

		if ($this->receiverPlayerID !== null) {
			$receiver = Player::getPlayer($this->receiverPlayerID)->getDisplayName();
		} else {
			$receiver = 'All Online';
		}

		$template->pageRenderer = fn() => CommonMessageSendRenderer::render(
			Receiver: $receiver,
			MessageSendPage: new MessageSendProcessor($this->receiverPlayerID),
			Preview: $this->preview,
			ThisPlayer: $player,
		);
	}

}
