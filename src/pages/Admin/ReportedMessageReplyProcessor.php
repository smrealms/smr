<?php declare(strict_types=1);

namespace Smr\Pages\Admin;

use Smr\Account;
use Smr\Html\Submit;
use Smr\Page\AccountPageProcessor;
use Smr\Player;
use Smr\Request;

class ReportedMessageReplyProcessor extends AccountPageProcessor {

	private const string ACTION = 'action';

	public readonly Submit $actionSend;
	public readonly Submit $actionPreview;

	public function __construct(
		private readonly int $gameID,
		private readonly int $offenderAccountID,
		private readonly int $offendedAccountID,
	) {
		$this->actionSend = new Submit(self::ACTION, 'Send messages');
		$this->actionPreview = new Submit(self::ACTION, 'Preview messages');
	}

	public function build(Account $account): never {
		$offenderReply = Request::get('offenderReply');
		$offenderBanPoints = Request::getInt('offenderBanPoints');
		$offendedReply = Request::get('offendedReply');
		$offendedBanPoints = Request::getInt('offendedBanPoints');
		if (Request::get(self::ACTION) === $this->actionPreview->value) {
			$container = new ReportedMessageReply(
				offenderAccountID: $this->offenderAccountID,
				offendedAccountID: $this->offendedAccountID,
				gameID: $this->gameID,
				offenderPreview: $offenderReply,
				offenderBanPoints: $offenderBanPoints,
				offendedPreview: $offendedReply,
				offendedBanPoints: $offendedBanPoints,
			);
			$container->go();
		}

		if ($offenderReply !== '') {
			Player::sendMessageFromAdmin($this->gameID, $this->offenderAccountID, $offenderReply);

			//do we have points?
			if ($offenderBanPoints > 0) {
				$suspicion = 'Inappropriate In-Game Message';
				$offenderAccount = Account::getAccount($this->offenderAccountID);
				$offenderAccount->addPoints($offenderBanPoints, $account, BAN_REASON_BAD_BEHAVIOR, $suspicion);
			}
		}

		if ($offendedReply !== '') {
			//next message
			Player::sendMessageFromAdmin($this->gameID, $this->offendedAccountID, $offendedReply);

			//do we have points?
			if ($offendedBanPoints > 0) {
				$suspicion = 'Inappropriate In-Game Message';
				$offendedAccount = Account::getAccount($this->offendedAccountID);
				$offendedAccount->addPoints($offendedBanPoints, $account, BAN_REASON_BAD_BEHAVIOR, $suspicion);
			}
		}
		new ReportedMessageView()->go();
	}

}
