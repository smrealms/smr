<?php declare(strict_types=1);

class AdminPermissions {

	// The array keys must not be changed because they are referred to
	// in the `account_has_permission` database table.
	// Info is [Permission Name, Page to Link, Category].
	private const PERMISSION_TABLE = [
		1  => ['Manage Admin Permissions', 'permission_manage.php', 3],
		2  => ['Database Cleanup', 'db_cleanup.php', 3],
		3  => ['Server Open/Close', 'game_status.php', 3],
		4  => ['Delete Game', 'game_delete.php', 5],
		5  => ['Create Announcement', 'announcement_create.php', 3],
		6  => ['Send Message', 'admin_message_send_select.php', 3],
		7  => ['View Reported Messages', 'notify_view.php', 1],
		8  => ['Edit Account', 'account_edit_search.php', 1],
		9  => ['Multi Tools', 'ip_view.php', 1],
		12 => ['Cheating Ship Check', 'ship_check.php', 1],
		16 => ['Log Console', 'log_console.php', 1],
		17 => ['Send Newsletter', 'newsletter_send.php', 3],
		18 => ['Form Access', 'form_open.php', 3],
		19 => ['Approve Photo Album', 'album_approve.php', 2],
		20 => ['Moderate Photo Album', 'album_moderate_select.php', 2],
		21 => ['Manage ChangeLog', 'changelog.php', 3],
		22 => ['Anon Account View', 'anon_acc_view_select.php', 1],
		23 => ['Word Filter', 'word_filter.php', 1],
		24 => ['Combat Simulator', 'combat_simulator.php', 4],
		25 => ['Edit Locations', 'location_edit.php', 4],
		26 => ['View Message Boxes', 'box_view.php', 1],
		27 => ['Can Moderate Feature Requests', '', 2],
		28 => ['Can Edit Alliance Descriptions', '', 1],
		30 => ['1.6 Universe Generator', '1.6/game_create.php', 5],
		31 => ['Create Vote', 'vote_create.php', 3],
		32 => ['Can Edit Started Games', '', 5],
		33 => ['Enable Games', 'enable_game.php', 5],
		34 => ['Manage Galactic Post Editors', 'manage_post_editors.php', 5],
		35 => ['Manage Draft Leaders', 'manage_draft_leaders.php', 5],
		36 => ['Display Admin Tag', '', 2],
		37 => ['Manage NPCs', 'npc_manage.php', 5],
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
	public static function getPermissionInfo($permissionID) {
		return self::PERMISSION_TABLE[$permissionID];
	}

	/**
	 * Returns a list of all permissions with ID keys and name values,
	 * grouped by the category ID of the permission.
	 */
	public static function getPermissionsByCategory() {
		$result = [];
		foreach (self::PERMISSION_TABLE as $permissionID => $info) {
			$categoryID = $info[2];
			$result[$categoryID][$permissionID] = $info[0];
		}
		return $result;
	}

	public static function getCategoryName($categoryID) {
		return self::PERMISSION_CATEGORIES[$categoryID];
	}

}
