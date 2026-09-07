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
		private readonly int $offenderPlayerID,
		private readonly int $offendedPlayerID,
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
				offenderPlayerID: $this->offenderPlayerID,
				offendedPlayerID: $this->offendedPlayerID,
				offenderPreview: $offenderReply,
				offenderBanPoints: $offenderBanPoints,
				offendedPreview: $offendedReply,
				offendedBanPoints: $offendedBanPoints,
			);
			$container->go();
		}

		if ($offenderReply !== '') {
			$offenderPlayer = Player::getPlayer($this->offenderPlayerID);
			Player::sendMessageFromAdmin($this->offenderPlayerID, $offenderReply);

			//do we have points?
			if ($offenderBanPoints > 0) {
				$suspicion = 'Inappropriate In-Game Message';
				$offenderAccount = $offenderPlayer->getAccount();
				$offenderAccount->addPoints($offenderBanPoints, $account, BAN_REASON_BAD_BEHAVIOR, $suspicion);
			}
		}

		if ($offendedReply !== '') {
			//next message
			$offendedPlayer = Player::getPlayer($this->offendedPlayerID);
			Player::sendMessageFromAdmin($this->offendedPlayerID, $offendedReply);

			//do we have points?
			if ($offendedBanPoints > 0) {
				$suspicion = 'Inappropriate In-Game Message';
				$offendedAccount = $offendedPlayer->getAccount();
				$offendedAccount->addPoints($offendedBanPoints, $account, BAN_REASON_BAD_BEHAVIOR, $suspicion);
			}
		}
		new ReportedMessageView()->go();
	}

}
