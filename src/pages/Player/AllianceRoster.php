<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use Smr\Alliance;
use Smr\Database;
use Smr\Menu;
use Smr\Page\PlayerPage;
use Smr\Page\ReusableTrait;
use Smr\Player;
use Smr\Template;

class AllianceRoster extends PlayerPage {

	use ReusableTrait;
	public function __construct(
		private readonly ?int $allianceID = null,
		private readonly bool $showRoles = false,
	) {}

	public function build(Player $player, Template $template): void {
		$db = Database::getInstance();
		$account = $player->getAccount();

		$allianceID = $this->allianceID ?? $player->getAllianceID();

		$alliance = Alliance::getAlliance($allianceID, $player->getGameID());

		$template->pageTopic = $alliance->getAllianceDisplayName(false, true);
		Menu::alliance($alliance->getAllianceID());

		if ($this->showRoles) {
			// initialize with text
			$roles = [];

			// get all roles from db for faster access later
			$dbResult = $db->select('alliance_has_roles', $alliance->SQLID, ['role_id', 'role']);
			foreach ($dbResult->records() as $dbRecord) {
				$roles[$dbRecord->getInt('role_id')] = $dbRecord->getString('role');
			}

			$saveAllianceRolesHREF = new AllianceRolesSaveProcessor($allianceID)->href();
		} else {
			$roles = null;
			$saveAllianceRolesHREF = null;
		}

		$dbResult = $db->read(
			'SELECT
			SUM(experience) AS alliance_xp,
			FLOOR(AVG(experience)) AS alliance_avg
			FROM player
			WHERE alliance_id = :alliance_id
			AND game_id = :game_id
			GROUP BY alliance_id',
			$alliance->SQLID,
		);
		$dbRecord = $dbResult->record();

		if ($account->getAccountID() === $alliance->getLeaderID() || $account->hasPermission(PERMISSION_EDIT_ALLIANCE_DESCRIPTION)) {
			$editAllianceDescriptionHREF = new AllianceGovernance($allianceID)->href();
		} else {
			$editAllianceDescriptionHREF = null;
		}

		$dbResult = $db->select('alliance_has_roles', [
			...$alliance->SQLID,
			'change_roles' => $db->escapeBoolean(true),
			'role_id' => $player->getAllianceRole(),
		]);
		$allowed = $dbResult->hasRecord();

		$alliancePlayers = $alliance->getMembers(includeNpc: true);

		if ($alliance->getAllianceID() === $player->getAllianceID()) {
			// Alliance members get to see active/inactive status of members
			$activeIDs = $alliance->getActiveIDs();
			$toggleRolesHREF = new self($this->allianceID, !$this->showRoles)->href();
		} else {
			$activeIDs = null;
			$toggleRolesHREF = null;
		}

		// If the player is already in an alliance, we don't want to print
		// any messages, so we simply omit the "join alliance" section.
		$joinRestriction = $player->hasAlliance() ? true : $alliance->getJoinRestriction($player);
		if ($joinRestriction === false) {
			$joinHREF = new AllianceJoinProcessor($allianceID)->href();
		} else {
			$joinHREF = null;
		}

		$template->pageRenderer = fn() => AllianceRosterRenderer::render(
			template: $template,
			Alliance: $alliance,
			Roles: $roles,
			SaveAllianceRolesHREF: $saveAllianceRolesHREF,
			AllianceExp: $dbRecord->getInt('alliance_xp'),
			AllianceAverageExp: $dbRecord->getInt('alliance_avg'),
			EditAllianceDescriptionHREF: $editAllianceDescriptionHREF,
			CanChangeRoles: $allowed,
			AlliancePlayers: $alliancePlayers,
			ActiveIDs: $activeIDs,
			ToggleRolesHREF: $toggleRolesHREF,
			JoinRestriction: $joinRestriction,
			JoinHREF: $joinHREF,
			ThisAccount: $account,
			ThisPlayer: $player,
		);
	}

}
