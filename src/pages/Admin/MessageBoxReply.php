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
		private readonly int $senderAccountID,
		private readonly int $gameID,
		private readonly ?string $preview = null,
		private readonly int $banPoints = 0,
		private readonly int $rewardCredits = 0,
	) {}

	public function build(Account $account, Template $template): void {
		$boxName = Messages::getAdminBoxNames()[$this->boxTypeID];
		$template->pageTopic = 'Reply To ' . $boxName;

		$template->pageRenderer = fn() => MessageBoxReplyRenderer::render(
			BoxReplyFormPage: new MessageBoxReplyProcessor(
				senderAccountID: $this->senderAccountID,
				gameID: $this->gameID,
				boxTypeID: $this->boxTypeID,
			),
			Sender: Player::getPlayer($this->senderAccountID, $this->gameID),
			SenderAccount: Account::getAccount($this->senderAccountID),
			Preview: $this->preview,
			BanPoints: $this->banPoints,
			RewardCredits: $this->rewardCredits,
			BackHREF: new MessageBoxView($this->boxTypeID)->href(),
		);
	}

}
