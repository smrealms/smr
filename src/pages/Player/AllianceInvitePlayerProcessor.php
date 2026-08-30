<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use Smr\Database;
use Smr\Epoch;
use Smr\Page\PlayerPageProcessor;
use Smr\Player;
use Smr\Request;

class AllianceInvitePlayerProcessor extends PlayerPageProcessor {

	public function build(Player $player): never {
		$account = $player->getAccount();

		$receiverPlayerNumber = Request::getInt('player_number');
		$receiverPlayerID = Player::getPlayerByPlayerNumber(
			playerNumber: $receiverPlayerNumber,
			gameID: $player->getGameID(),
		)->getPlayerID();
		$addMessage = Request::get('message');
		$expireDays = Request::getInt('expire_days');

		$expires = Epoch::time() + 86400 * $expireDays;

		// If sender is mail banned or blacklisted by receiver, omit the custom message
		$db = Database::getInstance();
		$dbResult = $db->select('message_blacklist', [
			'player_id' => $receiverPlayerID,
			'blacklisted_player_id' => $player->getPlayerID(),
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

		$player->sendAllianceInvitation(
			receiverPlayerID: $receiverPlayerID,
			message: $msg,
			expires: $expires,
		);

		$container = new AllianceInvitePlayer();
		$container->go();
	}

}
