<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use Smr\AbstractPlayer;
use Smr\Alliance;
use Smr\Database;
use Smr\Menu;
use Smr\Page\PlayerPage;
use Smr\Page\ReusableTrait;
use Smr\Template;

class AllianceRoster extends PlayerPage {

	use ReusableTrait;

	public string $file = 'alliance_roster.php';

	public function __construct(
		private readonly ?int $allianceID = null,
		private readonly bool $showRoles = false
	) {}

	public function build(AbstractPlayer $player, Template $template): void {
		$db = Database::getInstance();
		$account = $player->getAccount();

		$allianceID = $this->allianceID ?? $player->getAllianceID();

		$alliance = Alliance::getAlliance($allianceID, $player->getGameID());
		$template->assign('Alliance', $alliance);

		$template->assign('PageTopic', $alliance->getAllianceDisplayName(false, true));
		Menu::alliance($alliance->getAllianceID());

		if ($this->showRoles) {
			// initialize with text
			$roles = [];

			// get all roles from db for faster access later
			$dbResult = $db->read('SELECT role_id, role
						FROM alliance_has_roles
						WHERE game_id = :game_id
						AND alliance_id = :alliance_id
						ORDER BY role_id', [
				'game_id' => $db->escapeNumber($alliance->getGameID()),
				'alliance_id' => $db->escapeNumber($alliance->getAllianceID()),
			]);
			foreach ($dbResult->records() as $dbRecord) {
				$roles[$dbRecord->getInt('role_id')] = $dbRecord->getString('role');
			}
			$template->assign('Roles', $roles);

			$container = new AllianceRolesSaveProcessor($allianceID);
			$template->assign('SaveAllianceRolesHREF', $container->href());
		}

		$dbResult = $db->read('SELECT
			SUM(experience) AS alliance_xp,
			FLOOR(AVG(experience)) AS alliance_avg
			FROM player
			WHERE alliance_id = :alliance_id
			AND game_id = :game_id
			GROUP BY alliance_id', [
			'game_id' => $db->escapeNumber($alliance->getGameID()),
			'alliance_id' => $db->escapeNumber($alliance->getAllianceID()),
		]);
		$dbRecord = $dbResult->record();

		$template->assign('AllianceExp', $dbRecord->getInt('alliance_xp'));
		$template->assign('AllianceAverageExp', $dbRecord->getInt('alliance_avg'));

		if ($account->getAccountID() === $alliance->getLeaderID() || $account->hasPermission(PERMISSION_EDIT_ALLIANCE_DESCRIPTION)) {
			$container = new AllianceGovernance($allianceID);
			$template->assign('EditAllianceDescriptionHREF', $container->href());
		}

		$dbResult = $db->read('SELECT 1 FROM alliance_has_roles WHERE alliance_id = :alliance_id AND game_id = :game_id
					AND role_id = :role_id AND change_roles = \'TRUE\'', [
			'game_id' => $db->escapeNumber($alliance->getGameID()),
			'alliance_id' => $db->escapeNumber($alliance->getAllianceID()),
			'role_id' => $db->escapeNumber($player->getAllianceRole()),
		]);
		$allowed = $dbResult->hasRecord();
		$template->assign('CanChangeRoles', $allowed);

		$alliancePlayers = $alliance->getMembers();
		$template->assign('AlliancePlayers', $alliancePlayers);

		if ($alliance->getAllianceID() === $player->getAllianceID()) {
			// Alliance members get to see active/inactive status of members
			$template->assign('ActiveIDs', $alliance->getActiveIDs());
			$container = new self($this->allianceID, !$this->showRoles);
			$template->assign('ToggleRolesHREF', $container->href());
		}

		// If the player is already in an alliance, we don't want to print
		// any messages, so we simply omit the "join alliance" section.
		$joinRestriction = $player->hasAlliance() ? true : $alliance->getJoinRestriction($player);
		$template->assign('JoinRestriction', $joinRestriction);
		if ($joinRestriction === false) {
			$container = new AllianceJoinProcessor($allianceID);
			$template->assign('JoinHREF', $container->href());
		}
	}

}
