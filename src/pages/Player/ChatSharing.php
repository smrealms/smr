<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use Smr\Account;
use Smr\Database;
use Smr\Exceptions\PlayerNotFound;
use Smr\Page\PlayerPage;
use Smr\Player;
use Smr\Template;

class ChatSharing extends PlayerPage {

	public function __construct(
		private readonly ?string $message = null,
	) {}

	public function build(Player $player, Template $template): void {
		$template->pageTopic = 'Chat Sharing Settings';

		$shareFrom = [];
		$db = Database::getInstance();
		$dbResult = $db->read('SELECT * FROM account_shares_info WHERE to_account_id = :account_id AND (game_id=0 OR game_id = :game_id)', [
			...$player->getAccount()->SQLID,
			'game_id' => $db->escapeNumber($player->getGameID()),
		]);
		foreach ($dbResult->records() as $dbRecord) {
			$fromAccountId = $dbRecord->getInt('from_account_id');
			$gameId = $dbRecord->getInt('game_id');
			try {
				$otherPlayer = Player::getPlayerByAccountAndGame($fromAccountId, $player->getGameID());
			} catch (PlayerNotFound) {
				// Player has not joined this game yet
				$otherPlayer = null;
			}
			$shareFrom[$fromAccountId] = [
				'Player Number' => $otherPlayer === null ? '-' : $otherPlayer->getPlayerNumber(),
				'Player Name' => (
					$otherPlayer === null ?
					'<b>Account</b>: ' . Account::getAccount($fromAccountId)->getHofDisplayName() :
					$otherPlayer->getDisplayName()
				),
				'All Games' => $gameId === 0 ? '<span class="green">YES</span>' : '<span class="red">NO</span>',
				'Game ID' => $gameId,
			];
		}

		$shareTo = [];
		$dbResult = $db->read('SELECT * FROM account_shares_info WHERE from_account_id = :account_id AND (game_id=0 OR game_id = :game_id)', [
			...$player->getAccount()->SQLID,
			'game_id' => $db->escapeNumber($player->getGameID()),
		]);
		foreach ($dbResult->records() as $dbRecord) {
			$gameId = $dbRecord->getInt('game_id');
			$toAccountId = $dbRecord->getInt('to_account_id');
			try {
				$otherPlayer = Player::getPlayerByAccountAndGame($toAccountId, $player->getGameID());
			} catch (PlayerNotFound) {
				// Player has not joined this game yet
				$otherPlayer = null;
			}
			$shareTo[$toAccountId] = [
				'Player Number' => $otherPlayer === null ? '-' : $otherPlayer->getPlayerNumber(),
				'Player Name' => (
					$otherPlayer === null ?
					'<b>Account</b>: ' . Account::getAccount($toAccountId)->getHofDisplayName() :
					$otherPlayer->getDisplayName()
				),
				'All Games' => $gameId === 0 ? '<span class="green">YES</span>' : '<span class="red">NO</span>',
				'Game ID' => $gameId,
			];
		}

		$template->pageRenderer = fn() => ChatSharingRenderer::render(
			Message: $this->message,
			ShareFrom: $shareFrom,
			ShareTo: $shareTo,
			ProcessingHREF: new ChatSharingProcessor(array_keys($shareTo))->href(),
		);
	}

}
