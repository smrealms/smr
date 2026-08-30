<?php declare(strict_types=1);

namespace Smr\Pages\Admin;

use Smr\Account;
use Smr\Messages;
use Smr\Page\AccountPage;
use Smr\Player;
use Smr\Template;

class MessageBoxReply extends AccountPage {

	public function __construct(
		private readonly int $boxTypeID,
		private readonly int $senderPlayerID,
		private readonly ?string $preview = null,
		private readonly int $banPoints = 0,
		private readonly int $rewardCredits = 0,
	) {}

	public function build(Account $account, Template $template): void {
		$boxName = Messages::getAdminBoxNames()[$this->boxTypeID];
		$template->pageTopic = 'Reply To ' . $boxName;

		$senderPlayer = Player::getPlayer($this->senderPlayerID);
		$template->pageRenderer = fn() => MessageBoxReplyRenderer::render(
			BoxReplyFormPage: new MessageBoxReplyProcessor(
				senderPlayerID: $this->senderPlayerID,
				boxTypeID: $this->boxTypeID,
			),
			Sender: $senderPlayer,
			SenderAccount: $senderPlayer->getAccount(),
			Preview: $this->preview,
			BanPoints: $this->banPoints,
			RewardCredits: $this->rewardCredits,
			BackHREF: new MessageBoxView($this->boxTypeID)->href(),
		);
	}

}
