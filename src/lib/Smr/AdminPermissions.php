<?php declare(strict_types=1);

namespace Smr;

class AdminPermissions {

	// The array keys must not be changed because they are referred to
	// in the `account_has_permission` database table.
	// Info is [Permission Name, Page to Link, Category].
	private const PERMISSION_TABLE = [
		1 => ['Manage Admin Permissions', 'admin/permission_manage.php', 3],
		2 => ['Database Cleanup', 'admin/db_cleanup.php', 3],
		3 => ['Server Open/Close', 'admin/game_status.php', 3],
		4 => ['Delete Game', 'admin/game_delete.php', 5],
		5 => ['Create Announcement', 'admin/announcement_create.php', 3],
		6 => ['Send Message', 'admin/admin_message_send_select.php', 3],
		7 => ['View Reported Messages', 'admin/notify_view.php', 1],
		8 => ['Edit Account', 'admin/account_edit_search.php', 1],
		9 => ['Multi Tools', 'admin/ip_view.php', 1],
		12 => ['Cheating Ship Check', 'admin/ship_check.php', 1],
		16 => ['Log Console', 'admin/log_console.php', 1],
		17 => ['Send Newsletter', 'admin/newsletter_send.php', 3],
		18 => ['Form Access', 'admin/form_open.php', 3],
		19 => ['Approve Photo Album', 'admin/album_approve.php', 2],
		20 => ['Moderate Photo Album', 'admin/album_moderate_select.php', 2],
		21 => ['Manage ChangeLog', 'admin/changelog.php', 3],
		22 => ['Anon Account View', 'admin/anon_acc_view_select.php', 1],
		23 => ['Word Filter', 'admin/word_filter.php', 1],
		24 => ['Combat Simulator', 'admin/combat_simulator.php', 4],
		25 => ['Edit Locations', 'admin/location_edit.php', 4],
		26 => ['View Message Boxes', 'admin/box_view.php', 1],
		27 => ['Can Moderate Feature Requests', '', 2],
		28 => ['Can Edit Alliance Descriptions', '', 1],
		30 => ['Universe Generator', 'admin/unigen/game_create.php', 5],
		31 => ['Create Vote', 'admin/vote_create.php', 3],
		32 => ['Can Edit Started Games', '', 5],
		33 => ['Enable Games', 'admin/enable_game.php', 5],
		34 => ['Manage Galactic Post Editors', 'admin/manage_post_editors.php', 5],
		35 => ['Manage Draft Leaders', 'admin/manage_draft_leaders.php', 5],
		36 => ['Display Admin Tag', '', 2],
		37 => ['Manage NPCs', 'admin/npc_manage.php', 5],
	];

	private const PERMISSION_CATEGORIES = [
		1 => 'Monitor Players',
		2 => 'Community Services',
		3 => 'Administrative',
		4 => 'Miscellaneous',
		5 => 'Manage Games',
	];

	/**
	 * Returns the info for the admin permission with the given ID.
	 */
	public static function getPermissionInfo(int $permissionID): array {
		return self::PERMISSION_TABLE[$permissionID];
	}

	/**
	 * Returns a list of all permissions with ID keys and name values,
	 * grouped by the category ID of the permission.
	 */
	public static function getPermissionsByCategory(): array {
		$result = [];
		foreach (self::PERMISSION_TABLE as $permissionID => $info) {
			$categoryID = $info[2];
			$result[$categoryID][$permissionID] = $info[0];
		}
		return $result;
	}

	public static function getCategoryName(int $categoryID): string {
		return self::PERMISSION_CATEGORIES[$categoryID];
	}

}
