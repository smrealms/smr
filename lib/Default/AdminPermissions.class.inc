<?php

class AdminPermissions {

	// The array keys must not be changed because they are referred to
	// in the `account_has_permission` database table.
	private const PERMISSION_TABLE = [
		1  => ['Manage Admin Permissions', 'permission_manage.php'],
		3  => ['Game Open/Close', 'game_status.php'],
		4  => ['Delete Game', 'game_delete.php'],
		5  => ['Create Announcement', 'announcement_create.php'],
		6  => ['Send Message', 'admin_message_send.php'],
		7  => ['View Reported Messages', 'notify_view.php'],
		8  => ['Edit Account', 'account_edit.php'],
		9  => ['Multi Tools', 'ip_view.php'],
		10 => ['Check Passwords', 'password_check.php'],
		11 => ['Check Info', 'info.php'],
		12 => ['Cheating Ship Check', 'ship_check.php'],
		13 => ['Show Map to User', 'map_show.php'],
		14 => ['Create SMC File', 'smc_choose.php'],
		15 => ['Search for Keywords', 'keyword_search.php'],
		16 => ['Log Console', 'log_console.php'],
		17 => ['Send Newsletter', 'newsletter_send.php'],
		18 => ['Form Access', 'form_open.php'],
		19 => ['Approve Photo Album', 'album_approve.php'],
		20 => ['Moderate Photo Album', 'album_moderate.php'],
		21 => ['Manage ChangeLog', 'changelog.php'],
		22 => ['Anon Account View', 'anon_acc_view.php'],
		23 => ['Word Filter', 'word_filter.php'],
		24 => ['Combat Simulator', 'combat_simulator.php'],
		25 => ['Edit Locations', 'location_edit.php'],
		26 => ['View Message Boxes', 'box_view.php'],
		27 => ['Can Moderate Feature Requests', ''],
		28 => ['Can Edit Alliance Descriptions', ''],
		30 => ['1.6 Universe Generator', '1.6/universe_create.php'],
		31 => ['Create Vote', 'vote_create.php'],
		32 => ['Can Edit Started Games', ''],
		33 => ['Enable Games', 'enable_game.php'],
		34 => ['Manage Galactic Post Editors', 'manage_post_editors.php'],
		35 => ['Manage Draft Leaders', 'manage_draft_leaders.php'],
	];

	/**
	 * Returns the info for the admin permission with the given ID.
	 */
	static function getPermissionInfo($permissionID) {
		return self::PERMISSION_TABLE[$permissionID];
	}

	/**
	 * Returns a list of all permissions with ID keys and name values.
	 */
	static function getPermissionList() {
		$result = [];
		foreach (self::PERMISSION_TABLE as $permissionID => $info) {
			$result[$permissionID] = $info[0];
		}
		return $result;
	}

}
