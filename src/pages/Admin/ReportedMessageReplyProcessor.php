<?php declare(strict_types=1);

namespace Smr\Pages\Admin;

use Smr\Account;
use Smr\Page\AccountPageProcessor;
use Smr\Player;
use Smr\Request;

class ReportedMessageReplyProcessor extends AccountPageProcessor {

	public function __construct(
		private readonly int $gameID,
		private readonly int $offenderAccountID,
		private readonly int $offendedAccountID
	) {}

	public function build(Account $account): never {
		$offenderReply = Request::get('offenderReply');
		$offenderBanPoints = Request::getInt('offenderBanPoints');
		$offendedReply = Request::get('offendedReply');
		$offendedBanPoints = Request::getInt('offendedBanPoints');
		if (Request::get('action') == 'Preview messages') {
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

		if ($offenderReply != '') {
			Player::sendMessageFromAdmin($this->gameID, $this->offenderAccountID, $offenderReply);

			//do we have points?
			if ($offenderBanPoints > 0) {
				$suspicion = 'Inappropriate In-Game Message';
				$offenderAccount = Account::getAccount($this->offenderAccountID);
				$offenderAccount->addPoints($offenderBanPoints, $account, BAN_REASON_BAD_BEHAVIOR, $suspicion);
			}
		}

		if ($offendedReply != '') {
			//next message
			Player::sendMessageFromAdmin($this->gameID, $this->offendedAccountID, $offendedReply);

			//do we have points?
			if ($offendedBanPoints > 0) {
				$suspicion = 'Inappropriate In-Game Message';
				$offendedAccount = Account::getAccount($this->offendedAccountID);
				$offendedAccount->addPoints($offendedBanPoints, $account, BAN_REASON_BAD_BEHAVIOR, $suspicion);
			}
		}
		(new ReportedMessageView())->go();
	}

}
