<?php declare(strict_types=1);

namespace Smr\Pages\Player\Chess;

use Smr\AbstractPlayer;
use Smr\Chess\ChessGame;
use Smr\Database;
use Smr\Page\PlayerPage;
use Smr\Page\ReusableTrait;
use Smr\Template;

class MatchList extends PlayerPage {

	use ReusableTrait;

	public string $file = 'chess.php';

	public function build(AbstractPlayer $player, Template $template): void {
		$chessGames = ChessGame::getOngoingPlayerGames($player);
		$template->assign('ChessGames', $chessGames);
		$template->assign('PageTopic', 'Casino');

		$playersChallenged = [$player->getAccountID() => true];
		foreach ($chessGames as $chessGame) {
			$playersChallenged[$chessGame->getWhiteID()] = true;
			$playersChallenged[$chessGame->getBlackID()] = true;
		}

		$players = [];
		$db = Database::getInstance();
		$dbResult = $db->read('SELECT player_id, player.player_name FROM player JOIN account USING(account_id) WHERE npc = :npc AND validated = :validated AND game_id = :game_id AND account_id NOT IN (:account_ids) ORDER BY player_name', [
			'npc' => $db->escapeBoolean(false),
			'validated' => $db->escapeBoolean(true),
			'game_id' => $db->escapeNumber($player->getGameID()),
			'account_ids' => $db->escapeArray(array_keys($playersChallenged)),
		]);
		foreach ($dbResult->records() as $dbRecord) {
			$players[$dbRecord->getInt('player_id')] = htmlentities($dbRecord->getString('player_name'));
		}
		$template->assign('PlayerList', $players);

		if (ENABLE_NPCS_CHESS) {
			$npcs = [];
			$dbResult = $db->read('SELECT player_id, player.player_name FROM player WHERE npc = :npc AND game_id = :game_id AND account_id NOT IN (:account_ids) ORDER BY player_name', [
				'npc' => $db->escapeBoolean(true),
				'game_id' => $db->escapeNumber($player->getGameID()),
				'account_ids' => $db->escapeArray(array_keys($playersChallenged)),
			]);
			foreach ($dbResult->records() as $dbRecord) {
				$npcs[$dbRecord->getInt('player_id')] = htmlentities($dbRecord->getString('player_name'));
			}
			$template->assign('NPCList', $npcs);
		}
	}

}
