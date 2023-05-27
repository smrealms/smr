<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use Smr\AbstractPlayer;
use Smr\Menu;
use Smr\Page\PlayerPage;
use Smr\Page\ReusableTrait;
use Smr\Player;
use Smr\Template;

class MessageSend extends PlayerPage {

	use ReusableTrait;

	public string $file = 'message_send.php';

	public function __construct(
		private readonly ?int $receiverAccountID = null,
		private readonly ?string $preview = null,
	) {}

	public function build(AbstractPlayer $player, Template $template): void {
		$template->assign('PageTopic', 'Send Message');

		Menu::messages();

		if ($this->receiverAccountID !== null) {
			$template->assign('Receiver', Player::getPlayer($this->receiverAccountID, $player->getGameID())->getDisplayName());
		} else {
			$template->assign('Receiver', 'All Online');
		}

		$container = new MessageSendProcessor($this->receiverAccountID);
		$template->assign('MessageSendFormHref', $container->href());

		if ($this->preview !== null) {
			$template->assign('Preview', $this->preview);
		}
	}

}
