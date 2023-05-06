<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use Smr\AbstractPlayer;
use Smr\Database;
use Smr\Exceptions\PlayerNotFound;
use Smr\Page\PlayerPageProcessor;
use Smr\Player;
use Smr\Request;

class ChatSharingProcessor extends PlayerPageProcessor {

	/**
	 * @param array<int> $shareAccountIDs Account IDs already being shared to
	 */
	public function __construct(
		private readonly array $shareAccountIDs
	) {}

	public function build(AbstractPlayer $player): never {
		$db = Database::getInstance();

		// Process adding a "share to" account
		if (Request::has('add')) {
			$addPlayerID = Request::getInt('add_player_id');
			if (empty($addPlayerID)) {
				error_on_page('You must specify a Player ID to share with!');
			}

			if ($addPlayerID === $player->getPlayerID()) {
				error_on_page('You do not need to share with yourself!');
			}

			try {
				$accountId = Player::getPlayerByPlayerID($addPlayerID, $player->getGameID())->getAccountID();
			} catch (PlayerNotFound $e) {
				error_on_page($e->getMessage());
			}

			if (in_array($accountId, $this->shareAccountIDs)) {
				error_on_page('You are already sharing with this player!');
			}

			$gameId = Request::has('all_games') ? 0 : $player->getGameID();
			$db->insert('account_shares_info', [
				'to_account_id' => $accountId,
				'from_account_id' => $player->getAccountID(),
				'game_id' => $gameId,
			]);
		}

		// Process removing a "share to" account
		if (Request::has('remove_share_to')) {
			$db->delete('account_shares_info', [
				'to_account_id' => Request::getInt('remove_share_to'),
				'from_account_id' => $player->getAccountID(),
				'game_id' => Request::getInt('game_id'),
			]);
		}

		// Process removing a "share from" account
		if (Request::has('remove_share_from')) {
			$db->delete('account_shares_info', [
				'to_account_id' => $player->getAccountID(),
				'from_account_id' => Request::getInt('remove_share_from'),
				'game_id' => Request::getInt('game_id'),
			]);
		}

		(new ChatSharing())->go();
	}

}

function error_on_page(string $message): never {
	$message = '<span class="bold red">ERROR:</span> ' . $message;
	(new ChatSharing($message))->go();
}
