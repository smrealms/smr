<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use AbstractSmrPlayer;
use Smr\Database;
use Smr\Exceptions\PlayerNotFound;
use Smr\Page\PlayerPage;
use Smr\Template;
use SmrAccount;
use SmrPlayer;

class ChatSharing extends PlayerPage {

	public string $file = 'chat_sharing.php';

	public function __construct(
		private readonly ?string $message = null
	) {}

	public function build(AbstractSmrPlayer $player, Template $template): void {
		$template->assign('PageTopic', 'Chat Sharing Settings');

		$template->assign('Message', $this->message);

		$shareFrom = [];
		$db = Database::getInstance();
		$dbResult = $db->read('SELECT * FROM account_shares_info WHERE to_account_id=' . $db->escapeNumber($player->getAccountID()) . ' AND (game_id=0 OR game_id=' . $db->escapeNumber($player->getGameID()) . ')');
		foreach ($dbResult->records() as $dbRecord) {
			$fromAccountId = $dbRecord->getInt('from_account_id');
			$gameId = $dbRecord->getInt('game_id');
			try {
				$otherPlayer = SmrPlayer::getPlayer($fromAccountId, $player->getGameID());
			} catch (PlayerNotFound) {
				// Player has not joined this game yet
				$otherPlayer = null;
			}
			$shareFrom[$fromAccountId] = [
				'Player ID' => $otherPlayer == null ? '-' : $otherPlayer->getPlayerID(),
				'Player Name' => $otherPlayer == null ?
				                 '<b>Account</b>: ' . SmrAccount::getAccount($fromAccountId)->getHofDisplayName() :
				                 $otherPlayer->getDisplayName(),
				'All Games' => $gameId == 0 ? '<span class="green">YES</span>' : '<span class="red">NO</span>',
				'Game ID' => $gameId,
			];
		}

		$shareTo = [];
		$dbResult = $db->read('SELECT * FROM account_shares_info WHERE from_account_id=' . $db->escapeNumber($player->getAccountID()) . ' AND (game_id=0 OR game_id=' . $db->escapeNumber($player->getGameID()) . ')');
		foreach ($dbResult->records() as $dbRecord) {
			$gameId = $dbRecord->getInt('game_id');
			$toAccountId = $dbRecord->getInt('to_account_id');
			try {
				$otherPlayer = SmrPlayer::getPlayer($toAccountId, $player->getGameID());
			} catch (PlayerNotFound) {
				// Player has not joined this game yet
				$otherPlayer = null;
			}
			$shareTo[$toAccountId] = [
				'Player ID' => $otherPlayer == null ? '-' : $otherPlayer->getPlayerID(),
				'Player Name' => $otherPlayer == null ?
				                 '<b>Account</b>: ' . SmrAccount::getAccount($toAccountId)->getHofDisplayName() :
				                 $otherPlayer->getDisplayName(),
				'All Games' => $gameId == 0 ? '<span class="green">YES</span>' : '<span class="red">NO</span>',
				'Game ID' => $gameId,
			];
		}

		$template->assign('ShareFrom', $shareFrom);
		$template->assign('ShareTo', $shareTo);

		$container = new ChatSharingProcessor(array_keys($shareTo));
		$template->assign('ProcessingHREF', $container->href());
	}

}
