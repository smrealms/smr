<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use Smr\AbstractPlayer;
use Smr\Database;
use Smr\Menu;
use Smr\Page\PlayerPage;
use Smr\Template;

class AllianceRoles extends PlayerPage {

	public string $file = 'alliance_roles.php';

	public function __construct(
		private readonly ?int $roleID = null
	) {}

	public function build(AbstractPlayer $player, Template $template): void {
		$alliance = $player->getAlliance();
		$template->assign('PageTopic', $alliance->getAllianceDisplayName(false, true));
		Menu::alliance($alliance->getAllianceID());

		$db = Database::getInstance();
		$dbResult = $db->read('SELECT *
		FROM alliance_has_roles
		WHERE game_id=' . $db->escapeNumber($alliance->getGameID()) . '
		AND alliance_id=' . $db->escapeNumber($alliance->getAllianceID()) . '
		ORDER BY role_id
		');
		$allianceRoles = [];
		foreach ($dbResult->records() as $dbRecord) {
			$roleID = $dbRecord->getInt('role_id');
			$allianceRoles[$roleID]['RoleID'] = $roleID;
			$allianceRoles[$roleID]['Name'] = $dbRecord->getString('role');
			$allianceRoles[$roleID]['EditingRole'] = $this->roleID === $roleID;
			$allianceRoles[$roleID]['CreatingRole'] = false;
			if ($allianceRoles[$roleID]['EditingRole']) {
				$container = new AllianceRolesProcessor($roleID);
				$allianceRoles[$roleID]['WithdrawalLimit'] = $dbRecord->getInt('with_per_day');
				$allianceRoles[$roleID]['PositiveBalance'] = $dbRecord->getBoolean('positive_balance');
				$allianceRoles[$roleID]['TreatyCreated'] = $dbRecord->getBoolean('treaty_created');
				$allianceRoles[$roleID]['RemoveMember'] = $dbRecord->getBoolean('remove_member');
				$allianceRoles[$roleID]['ChangePass'] = $dbRecord->getBoolean('change_pass');
				$allianceRoles[$roleID]['ChangeMod'] = $dbRecord->getBoolean('change_mod');
				$allianceRoles[$roleID]['ChangeRoles'] = $dbRecord->getBoolean('change_roles');
				$allianceRoles[$roleID]['PlanetAccess'] = $dbRecord->getBoolean('planet_access');
				$allianceRoles[$roleID]['ModerateMessageboard'] = $dbRecord->getBoolean('mb_messages');
				$allianceRoles[$roleID]['ExemptWithdrawals'] = $dbRecord->getBoolean('exempt_with');
				$allianceRoles[$roleID]['SendAllianceMessage'] = $dbRecord->getBoolean('send_alliance_msg');
				$allianceRoles[$roleID]['OpLeader'] = $dbRecord->getBoolean('op_leader');
				$allianceRoles[$roleID]['ViewBondsInPlanetList'] = $dbRecord->getBoolean('view_bonds');
			} else {
				$container = new self($roleID);
			}
			$allianceRoles[$roleID]['HREF'] = $container->href();
		}
		$template->assign('AllianceRoles', $allianceRoles);

		$template->assign('CreateRole', [
			'HREF' => (new AllianceRolesProcessor())->href(),
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
			'ViewBondsInPlanetList' => false]);
	}

}
