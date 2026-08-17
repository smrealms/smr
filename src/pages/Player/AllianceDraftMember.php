<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use Exception;
use Smr\Alliance;
use Smr\Database;
use Smr\Exceptions\AllianceNotFound;
use Smr\Game;
use Smr\Menu;
use Smr\Page\PlayerPage;
use Smr\Page\ReusableTrait;
use Smr\Player;
use Smr\Template;

class AllianceDraftMember extends PlayerPage {

	use ReusableTrait;
	public function build(Player $player, Template $template): void {
		if (!$player->getGame()->isGameType(Game::GAME_TYPE_DRAFT)) {
			throw new Exception('This page is only allowed in Draft games!');
		}

		$db = Database::getInstance();
		$alliance = $player->getAlliance();

		$template->pageTopic = $alliance->getAllianceDisplayName(false, true);
		Menu::alliance($alliance->getAllianceID());

		// Get the current teams
		require_once(LIB . 'Default/alliance_pick.inc.php');
		$teams = get_draft_teams($player->getGameID());

		// Add information about current player

		// If players were placed into the NHA, they are still eligible to be picked
		try {
			$NHA = Alliance::getAllianceByName(NHA_ALLIANCE_NAME, $player->getGameID());
			$NHAID = $NHA->getAllianceID();
		} catch (AllianceNotFound) {
			$NHAID = 0;
		}

		// Get a list of players still in the pick pool
		$players = [];
		$dbResult = $db->read('SELECT * FROM player WHERE game_id = :game_id AND (alliance_id=0 OR alliance_id = :nha_alliance_id) AND account_id NOT IN (SELECT account_id FROM draft_leaders WHERE draft_leaders.game_id=player.game_id) AND account_id NOT IN (SELECT picked_account_id FROM draft_history WHERE draft_history.game_id=player.game_id) AND account_id != :nhl_account_id', [
			'game_id' => $db->escapeNumber($player->getGameID()),
			'nha_alliance_id' => $db->escapeNumber($NHAID),
			'nhl_account_id' => $db->escapeNumber(ACCOUNT_ID_NHL),
		]);
		foreach ($dbResult->records() as $dbRecord) {
			$pickPlayer = Player::getPlayer($dbRecord->getInt('account_id'), $player->getGameID(), false, $dbRecord);
			$players[] = [
				'Player' => $pickPlayer,
				'HREF' => new AllianceDraftMemberProcessor($pickPlayer->getAccountID())->href(),
			];
		}

		// Get the draft history
		$history = [];
		$dbResult = $db->select('draft_history', ['game_id' => $player->getGameID()], orderBy: ['draft_id']);
		foreach ($dbResult->records() as $dbRecord) {
			$leader = Player::getPlayer($dbRecord->getInt('leader_account_id'), $player->getGameID());
			$pickedPlayer = Player::getPlayer($dbRecord->getInt('picked_account_id'), $player->getGameID());
			$history[] = [
				'Leader' => $leader,
				'Player' => $pickedPlayer,
				'Time' => $dbRecord->getInt('time'),
			];
		}

		$template->pageRenderer = fn() => AllianceDraftMemberRenderer::render(
			template: $template,
			Teams: $teams,
			PlayerID: $player->getPlayerID(),
			CanPick: $teams[$player->getAccountID()]['CanPick'],
			PickPlayers: $players,
			History: $history,
			ThisAccount: $player->getAccount(),
		);
	}

}
