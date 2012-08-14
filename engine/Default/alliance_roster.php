<?php

require_once(get_file_loc('SmrAlliance.class.inc'));

if (!isset($var['alliance_id'])) {
	SmrSession::updateVar('alliance_id',$player->getAllianceID());
}

if(!isset($var['SortKey'])) {
	SmrSession::updateVar('SortKey','getExperience');
}
if(!isset($var['SortDesc'])) {
	SmrSession::updateVar('SortDesc',true);
}

$alliance =& SmrAlliance::getAlliance($var['alliance_id'],$player->getGameID());

$template->assign('PageTopic',$alliance->getAllianceName() . ' (' . $alliance->getAllianceID() . ')');
require_once(get_file_loc('menu.inc'));
create_alliance_menu($alliance->getAllianceID(),$alliance->getLeaderID());

$varAction = isset($var['action']) ? $var['action'] : '';
// Does anyone actually use these?
if ($varAction == 'Show Alliance Roles') {
	// initialize with text
	$roles = array();

	// get all roles from db for faster access later
	$db->query('SELECT role_id, role
				FROM alliance_has_roles
				WHERE game_id=' . $db->escapeNumber($alliance->getGameID()) . '
				AND alliance_id=' . $db->escapeNumber($alliance->getAllianceID()) . '
				ORDER BY role_id');
	while ($db->nextRecord()) {
		$roles[$db->getInt('role_id')] = $db->getField('role');
	}

	$container=create_container('alliance_roles_save_processing.php');
	$container['alliance_id'] = $alliance->getAllianceID();
	$form = create_form($container, 'Save Alliance Roles');
	$PHP_OUTPUT.= $form['form'];
}


// If the alliance is the player's alliance they get live information
// Otherwise it comes from the cache.
$db->query('SELECT
	sum(experience) as alliance_xp,
	floor(avg(experience)) as alliance_avg
	FROM player
	WHERE alliance_id=' . $db->escapeNumber($alliance->getAllianceID()) . '
	AND game_id = ' . $db->escapeNumber($alliance->getGameID()) . '
	GROUP BY alliance_id'
);

$db->nextRecord();

$PHP_OUTPUT.= '<div align="center">';
$PHP_OUTPUT.= bbifyMessage($alliance->getDescription());
if($account->hasPermission(PERMISSION_EDIT_ALLIANCE_DESCRIPTION)) {
	$PHP_OUTPUT.= '<br /><br />';
	$container=array();
	$container['url'] = 'skeleton.php';
	$container['body'] = 'alliance_stat.php';
	$container['alliance_id'] = $alliance->getAllianceID();
	$PHP_OUTPUT.=create_button($container,'Edit');
}
$PHP_OUTPUT.= '<br /><br />';

$PHP_OUTPUT.= '
<table class="standard inset">
	<tr>
		<th>Alliance Name</th>
		<th>Total Experience</th>
		<th>Average Experience</th>
		<th>Members</th>
	</tr>
	<tr class="bold">
		<td>';
$PHP_OUTPUT.= $alliance->getAllianceName();
$PHP_OUTPUT.= '
		</td>
		<td class="center shrink">';
$PHP_OUTPUT.= $db->getField('alliance_xp');
$PHP_OUTPUT.= '
		</td>
		<td class="center shrink">';
$PHP_OUTPUT.= $db->getField('alliance_avg');
$PHP_OUTPUT.= '
		</td>
		<td class="center shrink">';
$PHP_OUTPUT.= $alliance->getNumMembers();
$PHP_OUTPUT.= '
		</td>';
$PHP_OUTPUT.= '
	</tr>
</table>';

$PHP_OUTPUT.= '</div><br />';

$PHP_OUTPUT.= '<h2>Current Members</h2><br />';

$PHP_OUTPUT.= '<div align="center">';

$PHP_OUTPUT.= '
<table class="standard fullwidth">
	<tr>
	<th>&nbsp;</th>
	<th><a href="'.Globals::getAllianceRosterHREF($alliance->getAllianceID(),'getPlayerName',$var['SortKey']=='getPlayerName'?!$var['SortDesc']:false).'">Trader Name</a></th>
	<th><a href="'.Globals::getAllianceRosterHREF($alliance->getAllianceID(),'getRaceName',$var['SortKey']=='getRaceName'?!$var['SortDesc']:false).'">Race</a></th>
	<th><a href="'.Globals::getAllianceRosterHREF($alliance->getAllianceID(),'getExperience',$var['SortKey']=='getExperience'?!$var['SortDesc']:true).'">Experience</a></th>
';

// May want to make status a role in future but for now it's leaders only
if($player->getAccountID() == $alliance->getLeaderID()) {
	$PHP_OUTPUT.='<th class="shrink">Status</th>';
}

if($varAction == 'Show Alliance Roles') {
	$PHP_OUTPUT.= '<th>Role</th>';
}

$PHP_OUTPUT.= '</tr>';
$count = 1;

$db->query('SELECT * FROM player_has_alliance_role WHERE account_id = ' . $db->escapeNumber($player->getAccountID()) . ' AND game_id = ' . $db->escapeNumber($alliance->getGameID()) . ' AND alliance_id=' . $db->escapeNumber($alliance->getAllianceID()));
if ($db->nextRecord()) {
	$my_role_id = $db->getInt('role_id');
}
else {
	$my_role_id = 0;
}
$db->query('SELECT 1 FROM alliance_has_roles WHERE alliance_id = ' . $db->escapeNumber($alliance->getAllianceID()) . ' AND game_id = ' . $db->escapeNumber($alliance->getGameID()) . '
			AND role_id = ' . $db->escapeNumber($my_role_id) . ' AND change_roles = \'TRUE\'');
$allowed = $db->nextRecord();

$alliancePlayers =& SmrPlayer::getAlliancePlayers($player->getGameID(),$alliance->getAllianceID());

if($var['SortKey']!='getExperience' || $var['SortDesc']!==true) {
	Sorter::sortByNumMethod($alliancePlayers, $var['SortKey'], $var['SortDesc']);
}


foreach($alliancePlayers as &$alliancePlayer) {
	$class='';
	// check if this guy is the current guy
	if ($player->equals($alliancePlayer)) {
		$class .= 'bold';
	}
	if($alliancePlayer->getAccount()->isNewbie()) {
		$class.= ' newbie';
	}
	if($class!='') {
		$class = ' class="'.trim($class).'"';
	}
	$PHP_OUTPUT.= '<tr'.$class.'>';

	$PHP_OUTPUT.= '<td class="center shrink">';

	// counter
	if ($alliancePlayer->getAccountID() == $alliance->getLeaderID()) {
		$PHP_OUTPUT.= '*';
	}
	$PHP_OUTPUT.= ($count++);
	$PHP_OUTPUT.= '</td><td>';

	// player name
	$PHP_OUTPUT.= $alliancePlayer->getLevelName();
	$PHP_OUTPUT.= '&nbsp;';
	$PHP_OUTPUT.=$alliancePlayer->getLinkedDisplayName(false);
	$PHP_OUTPUT.= '</td><td class="center shrink">';

	// race name (colored)
	$container=create_container('skeleton.php', 'council_list.php');
	$container['race_id'] = $alliancePlayer->getRaceID();

	$PHP_OUTPUT.=create_link($container, $player->getColouredRaceName($alliancePlayer->getRaceID()));

	// xp
	$PHP_OUTPUT.= '</td><td class="shrink center">';
	$PHP_OUTPUT.= $alliancePlayer->getExperience();
	$PHP_OUTPUT.= '</td>';

	// Roles
	if ($varAction == 'Show Alliance Roles') {
		$PHP_OUTPUT.= '<td class="shrink right">';

		$db->query('SELECT role_id
					FROM player_has_alliance_role
					WHERE account_id=' . $db->escapeNumber($alliancePlayer->getAccountID()) . '
					AND game_id=' . $db->escapeNumber($player->getGameID()) . '
					AND alliance_id=' . $db->escapeNumber($alliancePlayer->getAllianceID()) . '
					LIMIT 1');

		if ($db->nextRecord()) {
			$role_id = $db->getInt('role_id');
		}
		else {
			$role_id = 0;
		}

		if ($allowed && $alliancePlayer->getAccountID() != $alliance->getLeaderID()) {
			$PHP_OUTPUT.= '<select name="role[' . $alliancePlayer->getAccountID() . ']" id="InputFields">';
			foreach ($roles as $curr_role_id => $role) {
				$PHP_OUTPUT.= '<option value="' . $curr_role_id .'"';
				if ($curr_role_id == $role_id) {
					$PHP_OUTPUT.= ' selected="selected"';
				}
				$PHP_OUTPUT.= '>';
				$PHP_OUTPUT.= stripslashes($role);
				$PHP_OUTPUT.= '</option>';
			}
			$PHP_OUTPUT.= '</select>';
		}
		else {
			$PHP_OUTPUT.= $roles[$role_id];
		}
		$PHP_OUTPUT.= '</td>';
	}
	
	/* Display ban status if alliance leader is viewing roster */
	if($player->getAccountID() == $alliance->getLeaderID()) {
		$allianceAccount = SmrAccount::getAccount($alliancePlayer->getAccountID());
		$disabled = $allianceAccount->isDisabled();
		if($disabled) {
			$PHP_OUTPUT .= '<td><p>Banned Until:<br/>' .  date(DATE_FULL_SHORT_SPLIT,$disabled['Time']) . '</p></td>';
		}
		else {
			$PHP_OUTPUT.= '<td class="center">Active</td>';
		}
	}
	
	$PHP_OUTPUT.= '</tr>';
} unset($alliancePlayer);

$PHP_OUTPUT.= '</table>';
$PHP_OUTPUT.= '</div>';

if ($alliance->getAllianceID() == $player->getAllianceID()) {
	$PHP_OUTPUT.= '<br /><h2>Options</h2><br />';
	$container=array();
	$container['url'] = 'skeleton.php';
	$container['body'] = 'alliance_roster.php';
	if($varAction == '' || $varAction == 'Hide Alliance Roles') {
		$container['action'] = 'Show Alliance Roles';
		$PHP_OUTPUT.=create_button($container,'Show Alliance Roles');
	}
	else {
		$PHP_OUTPUT.= $form['submit'];
		$PHP_OUTPUT.= '&nbsp;&nbsp;';
		$container['action'] = 'Hide Alliance Roles';
		$PHP_OUTPUT.=create_button($container,'Hide Alliance Roles');
		$PHP_OUTPUT.= '</form>';
	}
}

if (($canJoin = $alliance->canJoinAlliance($player)) === true) {
	$PHP_OUTPUT.= '<br />';
	$container = array();
	$container['url'] = 'alliance_join_processing.php';
	$container['alliance_id'] = $alliance->getAllianceID();
	$form = create_form($container, 'Join');
	$PHP_OUTPUT.= $form['form'];
	$PHP_OUTPUT.= 'Enter password to join alliance<br /><br />';
	$PHP_OUTPUT.= '<input type="password" name="password" size="30">&nbsp;';
	$PHP_OUTPUT.= $form['submit'];
	$PHP_OUTPUT.= '</form>';
}
else if($canJoin !== false) {
	$PHP_OUTPUT.= '<br />';
	$PHP_OUTPUT .= $canJoin;
}

?>
