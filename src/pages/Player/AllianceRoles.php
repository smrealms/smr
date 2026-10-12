<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use Smr\Database;
use Smr\Menu;
use Smr\Page\PlayerPage;
use Smr\Player;
use Smr\Template;

class AllianceRoles extends PlayerPage {

	public function __construct(
		private readonly ?int $roleID = null,
	) {}

	public function build(Player $player, Template $template): void {
		$alliance = $player->getAlliance();
		$template->pageTopic = $alliance->getAllianceDisplayName(false, true);
		Menu::alliance($alliance->getAllianceID());

		$db = Database::getInstance();
		$dbResult = $db->select('alliance_has_roles', $alliance->SQLID);
		$allianceRoles = [];
		foreach ($dbResult->records() as $dbRecord) {
			$roleID = $dbRecord->getInt('role_id');
			$commonKeys = [
				'RoleID' => $roleID,
				'Name' => $dbRecord->getString('role'),
				'CreatingRole' => false,
			];
			if ($this->roleID === $roleID) {
				$allianceRoles[$roleID] = [
					...$commonKeys,
					'EditingRole' => true,
					'HREF' => new AllianceRolesProcessor($roleID)->href(),
					'WithdrawalLimit' => $dbRecord->getInt('with_per_day'),
					'PositiveBalance' => $dbRecord->getBoolean('positive_balance'),
					'TreatyCreated' => $dbRecord->getBoolean('treaty_created'),
					'RemoveMember' => $dbRecord->getBoolean('remove_member'),
					'ChangePass' => $dbRecord->getBoolean('change_pass'),
					'ChangeMod' => $dbRecord->getBoolean('change_mod'),
					'ChangeRoles' => $dbRecord->getBoolean('change_roles'),
					'PlanetAccess' => $dbRecord->getBoolean('planet_access'),
					'ModerateMessageboard' => $dbRecord->getBoolean('mb_messages'),
					'ExemptWithdrawals' => $dbRecord->getBoolean('exempt_with'),
					'SendAllianceMessage' => $dbRecord->getBoolean('send_alliance_msg'),
					'OpLeader' => $dbRecord->getBoolean('op_leader'),
					'ViewBondsInPlanetList' => $dbRecord->getBoolean('view_bonds'),
					'ManageNpcs' => $dbRecord->getBoolean('manage_npcs'),
				];
			} else {
				$allianceRoles[$roleID] = [
					...$commonKeys,
					'EditingRole' => false,
					'HREF' => new self($roleID)->href(),
				];
			}
		}

		$CreateRole = [
			'HREF' => new AllianceRolesProcessor()->href(),
			'RoleID' => '',
			'Name' => '',
			'CreatingRole' => true,
			'EditingRole' => true,
			'WithdrawalLimit' => 0,
			'PositiveBalance' => false,
			'TreatyCreated' => false,
			'RemoveMember' => false,
			'ChangePass' => false,
			'ChangeMod' => false,
			'ChangeRoles' => false,
			'PlanetAccess' => true,
			'ModerateMessageboard' => false,
			'ExemptWithdrawals' => false,
			'SendAllianceMessage' => false,
			'OpLeader' => false,
			'ViewBondsInPlanetList' => false,
			'ManageNpcs' => true,
		];

		$template->pageRenderer = fn() => AllianceRolesRenderer::render(
			AllianceRoles: $allianceRoles,
			CreateRole: $CreateRole,
		);
	}

}
