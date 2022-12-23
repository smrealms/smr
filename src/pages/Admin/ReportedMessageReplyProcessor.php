<?php declare(strict_types=1);

namespace Smr\Pages\Admin;

use Smr\Page\AccountPageProcessor;
use Smr\Request;
use SmrAccount;
use SmrPlayer;

class ReportedMessageReplyProcessor extends AccountPageProcessor {

	public function __construct(
		private readonly int $gameID,
		private readonly int $offenderAccountID,
		private readonly int $offendedAccountID
	) {}

	public function build(SmrAccount $account): never {
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
				offendedBanPoints: $offendedBanPoints
			);
			$container->go();
		}

		if ($offenderReply != '') {
			SmrPlayer::sendMessageFromAdmin($this->gameID, $this->offenderAccountID, $offenderReply);

			//do we have points?
			if ($offenderBanPoints > 0) {
				$suspicion = 'Inappropriate In-Game Message';
				$offenderAccount = SmrAccount::getAccount($this->offenderAccountID);
				$offenderAccount->addPoints($offenderBanPoints, $account, BAN_REASON_BAD_BEHAVIOR, $suspicion);
			}
		}

		if ($offendedReply != '') {
			//next message
			SmrPlayer::sendMessageFromAdmin($this->gameID, $this->offendedAccountID, $offendedReply);

			//do we have points?
			if ($offendedBanPoints > 0) {
				$suspicion = 'Inappropriate In-Game Message';
				$offendedAccount = SmrAccount::getAccount($this->offendedAccountID);
				$offendedAccount->addPoints($offendedBanPoints, $account, BAN_REASON_BAD_BEHAVIOR, $suspicion);
			}
		}
		(new ReportedMessageView())->go();
	}

}
