<?php declare(strict_types=1);

namespace Smr\Pages\Admin;

use Smr\Account;
use Smr\Messages;
use Smr\Page\AccountPage;
use Smr\Player;
use Smr\Template;

class MessageBoxReply extends AccountPage {

	public string $file = 'admin/box_reply.php';

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
		$template->assign('PageTopic', 'Reply To ' . $boxName);

		$container = new MessageBoxReplyProcessor(
			senderAccountID: $this->senderAccountID,
			gameID: $this->gameID,
			boxTypeID: $this->boxTypeID,
		);
		$template->assign('BoxReplyFormHref', $container->href());
		$template->assign('Sender', Player::getPlayer($this->senderAccountID, $this->gameID));
		$template->assign('SenderAccount', Account::getAccount($this->senderAccountID));
		$template->assign('Preview', $this->preview);
		$template->assign('BanPoints', $this->banPoints);
		$template->assign('RewardCredits', $this->rewardCredits);

		$container = new MessageBoxView($this->boxTypeID);
		$template->assign('BackHREF', $container->href());
	}

}
