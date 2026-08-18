<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use Smr\Pages\Shared\AllianceRoleRenderer;

class AllianceRolesRenderer {

	/**
	 * @param array<int, array{RoleID: int, Name: string, EditingRole: bool, CreatingRole: bool, HREF: string, WithdrawalLimit?: int, PositiveBalance?: bool, TreatyCreated?: bool, RemoveMember?: bool, ChangePass?: bool, ChangeMod?: bool, ChangeRoles?: bool, PlanetAccess?: bool, ModerateMessageboard?: bool, ExemptWithdrawals?: bool, SendAllianceMessage?: bool, OpLeader?: bool, ViewBondsInPlanetList?: bool, ManageNpcs?: bool}> $AllianceRoles
	 * @param array{RoleID: string, Name: string, EditingRole: true, CreatingRole: true, HREF: string, WithdrawalLimit: int, PositiveBalance: bool, TreatyCreated: bool, RemoveMember: bool, ChangePass: bool, ChangeMod: bool, ChangeRoles: bool, PlanetAccess: bool, ModerateMessageboard: bool, ExemptWithdrawals: bool, SendAllianceMessage: bool, OpLeader: bool, ViewBondsInPlanetList: bool, ManageNpcs: bool} $CreateRole
	 */
	public static function render(array $AllianceRoles, array $CreateRole): void {
		?>
		<h2>Current Roles</h2><br /><?php
		foreach ($AllianceRoles as $Role) {
			AllianceRoleRenderer::render(Role: $Role);
		} ?><br />
		<h2>Create Role</h2><br /><?php
		AllianceRoleRenderer::render(Role: $CreateRole); ?>
		<b>Usage:</b><br />
		To add a new entry input the name of the role in the name field and press 'Create'.<br />
		To delete an entry clear the box and click 'Edit'.

		<?php
	}

}
