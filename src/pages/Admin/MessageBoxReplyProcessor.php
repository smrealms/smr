<?php declare(strict_types=1);

namespace Smr\Pages\Admin;

use Smr\Account;
use Smr\Html\Submit;
use Smr\Page\AccountPageProcessor;
use Smr\Player;
use Smr\Request;

class MessageBoxReplyProcessor extends AccountPageProcessor {

	private const string ACTION = 'action';

	public readonly Submit $actionSend;
	public readonly Submit $actionPreview;

	public function __construct(
		private readonly int $senderAccountID,
		private readonly int $gameID,
		private readonly int $boxTypeID,
	) {
		$this->actionSend = new Submit(self::ACTION, 'Send message');
		$this->actionPreview = new Submit(self::ACTION, 'Preview message');
	}

	public function build(Account $account): never {
		$message = Request::get('message');
		$banPoints = Request::getInt('BanPoints');
		$rewardCredits = Request::getInt('RewardCredits');
		if (Request::get(self::ACTION) === $this->actionPreview->value) {
			$container = new MessageBoxReply(
				boxTypeID: $this->boxTypeID,
				senderAccountID: $this->senderAccountID,
				gameID: $this->gameID,
				preview: $message,
				banPoints: $banPoints,
				rewardCredits: $rewardCredits,
			);
			$container->go();
		}

		Player::sendMessageFromAdmin($this->gameID, $this->senderAccountID, $message);

		$senderAccount = Account::getAccount($this->senderAccountID);
		$senderAccount->increaseSmrRewardCredits($rewardCredits);

		//do we have points?
		if ($banPoints > 0) {
			$suspicion = 'Inappropriate Actions';
			$senderAccount->addPoints($banPoints, $account, BAN_REASON_BAD_BEHAVIOR, $suspicion);
		}

		(new MessageBoxView())->go();
	}

}
