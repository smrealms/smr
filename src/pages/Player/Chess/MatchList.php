<?php declare(strict_types=1);

namespace Smr\Pages\Player\Chess;

use AbstractSmrPlayer;
use Smr\Chess\ChessGame;
use Smr\Database;
use Smr\Page\PlayerPage;
use Smr\Page\ReusableTrait;
use Smr\Template;

class MatchList extends PlayerPage {

	use ReusableTrait;

	public string $file = 'chess.php';

	public function build(AbstractSmrPlayer $player, Template $template): void {
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
		$dbResult = $db->read('SELECT player_id, player.player_name FROM player JOIN account USING(account_id) WHERE npc = ' . $db->escapeBoolean(false) . ' AND validated = ' . $db->escapeBoolean(true) . ' AND game_id = ' . $db->escapeNumber($player->getGameID()) . ' AND account_id NOT IN (' . $db->escapeArray(array_keys($playersChallenged)) . ') ORDER BY player_name');
		foreach ($dbResult->records() as $dbRecord) {
			$players[$dbRecord->getInt('player_id')] = htmlentities($dbRecord->getString('player_name'));
		}
		$template->assign('PlayerList', $players);

		if (ENABLE_NPCS_CHESS) {
			$npcs = [];
			$dbResult = $db->read('SELECT player_id, player.player_name FROM player WHERE npc = ' . $db->escapeBoolean(true) . ' AND game_id = ' . $db->escapeNumber($player->getGameID()) . ' AND account_id NOT IN (' . $db->escapeArray(array_keys($playersChallenged)) . ') ORDER BY player_name');
			foreach ($dbResult->records() as $dbRecord) {
				$npcs[$dbRecord->getInt('player_id')] = htmlentities($dbRecord->getString('player_name'));
			}
			$template->assign('NPCList', $npcs);
		}
	}

}
