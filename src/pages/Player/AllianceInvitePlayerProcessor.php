<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use Smr\AbstractPlayer;
use Smr\Database;
use Smr\Epoch;
use Smr\Page\PlayerPageProcessor;
use Smr\Request;

class AllianceInvitePlayerProcessor extends PlayerPageProcessor {

	public function build(AbstractPlayer $player): never {
		$account = $player->getAccount();

		$receiverID = Request::getInt('account_id');
		$addMessage = Request::get('message');
		$expireDays = Request::getInt('expire_days');

		$expires = Epoch::time() + 86400 * $expireDays;

		// If sender is mail banned or blacklisted by receiver, omit the custom message
		$db = Database::getInstance();
		$dbResult = $db->read('SELECT 1 FROM message_blacklist
		            WHERE account_id = :account_id
		              AND blacklisted_id = :blacklisted_id', [
			'account_id' => $db->escapeNumber($receiverID),
			'blacklisted_id' => $db->escapeNumber($player->getAccountID()),
		]);
		if ($dbResult->hasRecord() || $account->isMailBanned()) {
			$addMessage = '';
		}

		// Construct the mail to send to the receiver
		$msg = 'You have been invited to join an alliance!
		This invitation will remain open for ' . pluralise($expireDays, 'day') . ' or until you join another alliance.
		If you are currently in an alliance, you will leave it if you accept this invitation.

		[join_alliance=' . $player->getAllianceID() . ']
		';
		if ($addMessage !== '') {
			$msg .= '<br />' . $addMessage;
		}

		$player->sendAllianceInvitation($receiverID, $msg, $expires);

		$container = new AllianceInvitePlayer();
		$container->go();
	}

}
