<?php declare(strict_types=1);

$db = Smr\Database::getInstance();
$session = Smr\Session::getInstance();
$var = $session->getCurrentVar();
$player = $session->getPlayer();

$alliance_id = $var['alliance_id'] ?? $player->getAllianceID();

// Checkbox inputs only post if they are checked
$unlimited = Smr\Request::has('unlimited');
$positiveBalance = Smr\Request::has('positive');
$changePass = Smr\Request::has('changePW');
$removeMember = Smr\Request::has('removeMember');
$changeMOD = Smr\Request::has('changeMoD');
$changeRoles = Smr\Request::has('changeRoles') || (isset($var['role_id']) && $var['role_id'] == ALLIANCE_ROLE_LEADER); //Leader can always change roles.
$planetAccess = Smr\Request::has('planets');
$mbMessages = Smr\Request::has('mbMessages');
$exemptWith = Smr\Request::has('exemptWithdrawals');
$sendAllMsg = Smr\Request::has('sendAllMsg');
$viewBonds = Smr\Request::has('viewBonds');
$opLeader = Smr\Request::has('opLeader');

if ($unlimited) {
	$withPerDay = ALLIANCE_BANK_UNLIMITED;
} else {
	$withPerDay = Smr\Request::getInt('maxWith');
}
if ($withPerDay < 0 && $withPerDay != ALLIANCE_BANK_UNLIMITED) {
	create_error('You must enter a number for max withdrawals per 24 hours.');
}
if ($withPerDay == ALLIANCE_BANK_UNLIMITED && $positiveBalance) {
	create_error('You cannot have both unlimited withdrawals and a positive balance limit.');
}

// with empty role the user wants to create a new entry
if (!isset($var['role_id'])) {
	// role empty too? that doesn't make sence
	if (empty(Smr\Request::get('role'))) {
		create_error('You must enter a role name if you want to create a new one.');
	}

	$db->lockTable('alliance_has_roles');

	// get last id
	$dbResult = $db->read('SELECT MAX(role_id)
				FROM alliance_has_roles
				WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . '
					AND alliance_id = ' . $db->escapeNumber($alliance_id));
	if ($dbResult->hasRecord()) {
		$role_id = $dbResult->record()->getInt('MAX(role_id)') + 1;
	}

	$db->insert('alliance_has_roles', [
		'alliance_id' => $db->escapeNumber($alliance_id),
		'game_id' => $db->escapeNumber($player->getGameID()),
		'role_id' => $db->escapeNumber($role_id),
		'role' => $db->escapeString(Smr\Request::get('role')),
		'with_per_day' => $db->escapeNumber($withPerDay),
		'positive_balance' => $db->escapeBoolean($positiveBalance),
		'remove_member' => $db->escapeBoolean($removeMember),
		'change_pass' => $db->escapeBoolean($changePass),
		'change_mod' => $db->escapeBoolean($changeMOD),
		'change_roles' => $db->escapeBoolean($changeRoles),
		'planet_access' => $db->escapeBoolean($planetAccess),
		'exempt_with' => $db->escapeBoolean($exemptWith),
		'mb_messages' => $db->escapeBoolean($mbMessages),
		'send_alliance_msg' => $db->escapeBoolean($sendAllMsg),
		'op_leader' => $db->escapeBoolean($opLeader),
		'view_bonds' => $db->escapeBoolean($viewBonds),
	]);
	$db->unlock();
} else {
	// if no role is given we delete that entry
	if (empty(Smr\Request::get('role'))) {
		if ($var['role_id'] == ALLIANCE_ROLE_LEADER) {
			create_error('You cannot delete the leader role.');
		} elseif ($var['role_id'] == ALLIANCE_ROLE_NEW_MEMBER) {
			create_error('You cannot delete the new member role.');
		}
		$db->write('DELETE FROM alliance_has_roles
					WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . '
					AND alliance_id = ' . $db->escapeNumber($alliance_id) . '
					AND role_id = ' . $db->escapeNumber($var['role_id']));
	// otherwise we update it
	} else {
		$db->write('UPDATE alliance_has_roles
					SET role = ' . $db->escapeString(Smr\Request::get('role')) . ',
					with_per_day = ' . $db->escapeNumber($withPerDay) . ',
					positive_balance = ' . $db->escapeBoolean($positiveBalance) . ',
					remove_member = ' . $db->escapeBoolean($removeMember) . ',
					change_pass = ' . $db->escapeBoolean($changePass) . ',
					change_mod = ' . $db->escapeBoolean($changeMOD) . ',
					change_roles = ' . $db->escapeBoolean($changeRoles) . ',
					planet_access = ' . $db->escapeBoolean($planetAccess) . ',
					exempt_with = ' . $db->escapeBoolean($exemptWith) . ',
					mb_messages = ' . $db->escapeBoolean($mbMessages) . ',
					send_alliance_msg = ' . $db->escapeBoolean($sendAllMsg) . ',
					op_leader = ' . $db->escapeBoolean($opLeader) . ',
					view_bonds = ' . $db->escapeBoolean($viewBonds) . '
					WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . '
						AND alliance_id = ' . $db->escapeNumber($alliance_id) . '
						AND role_id = ' . $db->escapeNumber($var['role_id']));

	}

}
$container = Page::create('skeleton.php', 'alliance_roles.php');
$container['alliance_id'] = $alliance_id;
$container->go();
