<?

if (isset($var['alliance_id']))
{
	$alliance_id=$var['alliance_id'];
}
else
{
	$alliance_id=$player->getAllianceID();
}

$db->query('SELECT alliance_name,leader_id,alliance_password FROM alliance WHERE game_id=' . SmrSession::$game_id . ' AND alliance_id=' . $alliance_id . ' LIMIT 1');
$db->next_record();
$smarty->assign('PageTopic',stripslashes($db->f('alliance_name')) . ' (' . $alliance_id . ')');
include(ENGINE . 'global/menue.inc');
$PHP_OUTPUT.=create_alliance_menue($alliance_id,$db->f('leader_id'));

$leader_id = $db->f('leader_id');
$password = $db->f('alliance_password');

$db2 = new SMR_DB();
$varAction = isset($var['action']) ? $var['action'] : '';
// Does anyone actually use these?
if ($varAction == 'Show Alliance Roles') {
	// initialize with text
	$roles = array();

	// get all roles from db for faster access later
	$db->query('SELECT role_id, role
				FROM alliance_has_roles
				WHERE game_id=' . SmrSession::$game_id . '
				AND alliance_id=' .  $alliance_id . '
				ORDER BY role_id'
				);
	while ($db->next_record()) $roles[$db->f('role_id')] = $db->f('role');

	$container=array();
	$container['url'] = 'alliance_roles_save.php';
	$container['body'] = '';
	$form = create_form($container, 'Save Alliance Roles');
}

$db->query('SELECT race_id, race_name FROM race');
while ($db->next_record()) $races[$db->f('race_id')] = $db->f('race_name');


// If the alliance is the player's alliance they get live information
// Otherwise it comes from the cache.
if($alliance_id == $player->getAllianceID()) {
	$db->query('SELECT 
		alliance.alliance_description as description,
		count(player_name) as alliance_member_count,
		sum(player.experience) as alliance_xp,
		floor(avg(player.experience)) as alliance_avg,
		alliance.alliance_name as alliance_name,
		player.alliance_id as alliance_id 
		FROM player, alliance
		WHERE player.alliance_id=' . $alliance_id  . '
		AND alliance.alliance_id=' . $alliance_id  . '
		AND player.game_id = ' . SmrSession::$game_id . '
		AND alliance.game_id = ' . SmrSession::$game_id . '
		GROUP BY alliance.alliance_id'
	);
}
else {
	$db->query('SELECT 
		alliance.alliance_description as description,
		count(player_name) as alliance_member_count,
		sum(player.experience) as alliance_xp,
		floor(avg(player.experience)) as alliance_avg,
		alliance.alliance_name as alliance_name,
		player.alliance_id as alliance_id 
		FROM player,alliance
		WHERE player.alliance_id=' . $alliance_id  . '
		AND alliance.alliance_id=' . $alliance_id  . '
		AND player.game_id = ' . SmrSession::$game_id . '
		AND alliance.game_id = ' . SmrSession::$game_id . '
		GROUP BY alliance.alliance_id'
	);
}

$db->next_record();

$member_count = $db->f('alliance_member_count');
$PHP_OUTPUT.= $form['form'];
$PHP_OUTPUT.= '<div align="center">';
$PHP_OUTPUT.= $db->f('description');
$PHP_OUTPUT.= '<br><br>';

$PHP_OUTPUT.= '
<table cellspacing="0" cellpadding="0" class="standard inset">
	<tr>
		<th>Alliance Name</th>
		<th>Total Experience</th>
		<th>Average Experience</th>
		<th>Members</th>
	</tr>
	<tr class="bold">
		<td>';
$PHP_OUTPUT.= stripslashes($db->f('alliance_name'));
$PHP_OUTPUT.= '
		</td>
		<td class="center shrink">';
$PHP_OUTPUT.= $db->f('alliance_xp');
$PHP_OUTPUT.= '
		</td>
		<td class="center shrink">';
$PHP_OUTPUT.= $db->f('alliance_avg');
$PHP_OUTPUT.= '
		</td>
		<td class="center shrink">';
$PHP_OUTPUT.= $db->f('alliance_member_count');
$PHP_OUTPUT.= '
		</td>';
$PHP_OUTPUT.= '
	</tr>
</table>';

$PHP_OUTPUT.= '</div><br>';

$PHP_OUTPUT.= '<h2>Current Members</h2><br>';

$PHP_OUTPUT.= '<div align="center">';

$PHP_OUTPUT.= '
<table cellspacing="0" cellpadding="0" class="standard fullwidth">
	<tr>
	<th>&nbsp;</th>
	<th>Trader Name</th>
	<th>Race</th>
	<th>Experience</th>
';

if($varAction == 'Show Alliance Roles') {
	$PHP_OUTPUT.= '<th>Role</th>';
}

$PHP_OUTPUT.= '</tr>';
if($alliance_id == $player->getAllianceID()) {
	$db->query('
	SELECT account_id,player_name,player_id,experience,alignment,race_id
	FROM player
	WHERE game_id = ' . SmrSession::$game_id . '
	AND alliance_id=' . $alliance_id . '
	ORDER BY experience DESC'
	);
}
else {
	$db->query('
		SELECT account_id,' .
		'player_name,' .
		'player_id,' .
		'experience,' .
		'alignment,' .
		'race_id ' .
		'FROM player ' .
		'WHERE ' .
		'player.game_id = ' . SmrSession::$game_id . ' ' .
		'AND alliance_id=' . $alliance_id . ' ' .
		'ORDER BY experience DESC'
	);
}
$count = 1;

$container=array();
$container['url']= 'skeleton.php';
$db2->query('SELECT * FROM player_has_alliance_role WHERE account_id = '.$player->getAccountID().' AND game_id = '.$player->getGameID());
if ($db2->next_record()) $my_role_id = $db2->f('role_id');
else $my_role_id = 0;
$db2->query('SELECT * FROM alliance_has_roles WHERE alliance_id = '.$player->getAllianceID().' AND game_id = '.$player->getGameID().' AND ' . 
					'role_id = '.$my_role_id.' AND change_roles = \'TRUE\'');
if ($db2->next_record()) $allowed = TRUE;
while ($db->next_record()) {
	// check if this guy is the current guy
	if ( $db->f('account_id') == SmrSession::$account_id)
		$PHP_OUTPUT.= '<tr class="bold">';
	else
		$PHP_OUTPUT.= '<tr>';

	$PHP_OUTPUT.= '<td class="center shrink">';

	// counter
	if ($db->f('account_id') == $leader_id) $PHP_OUTPUT.= '*';
	$PHP_OUTPUT.= ($count++);
	$PHP_OUTPUT.= '</td><td>';

	// player name
	$db2->query('SELECT level_name
				FROM level
				WHERE requirement<=' . $db->f('experience') . '
				ORDER BY requirement DESC LIMIT 1'
				);
	$db2->next_record();

	$PHP_OUTPUT.= $db2->f('level_name');
	$PHP_OUTPUT.= '&nbsp;';
	$container['body'] = 'trader_search_result.php';
	$container['player_id'] = $db->f('player_id');
	$PHP_OUTPUT.=create_link($container, get_colored_text($db->f('alignment'),stripslashes($db->f('player_name')) . '&nbsp;(' . $db->f('player_id') . ')'));
	$PHP_OUTPUT.= '</td><td class="center shrink">';

	// race name (colored)
	$container['body'] = 'council_list.php';
	$container['race_id']	= $db->f('race_id');
	$container['race_name']	= $races[$db->f('race_id')];
	unset($container['player_id']);

	$PHP_OUTPUT.=create_link($container, $player->getColoredRaceName($db->f('race_id')));

	// xp
	$PHP_OUTPUT.= '</td><td class="shrink center">';
	$PHP_OUTPUT.= $db->f('experience');
	$PHP_OUTPUT.= '</td>';

	// Roles
	if ($varAction == 'Show Alliance Roles') {

		$PHP_OUTPUT.= '<td class="shrink right">';

		$db2 = new SMR_DB();
		$db2->query('SELECT role_id
					FROM player_has_alliance_role
					WHERE account_id=' . $db->f('account_id') .'
					AND game_id=' . SmrSession::$game_id . '
					LIMIT 1'
					);

		if ($db2->next_record())
			$role_id = $db2->f('role_id');
		else
			$role_id = 0;

		if ($allowed && $db->f('account_id') != $leader_id) {
		// ok do we display a select box or just a plain entry
		/*if (SmrSession::$account_id == $db->f('account_id') ||
			SmrSession::$account_id == $leader_id) {*/

			$PHP_OUTPUT.= '<select name="role[' . $db->f('account_id') . ']" id="InputFields">';
			foreach ($roles as $curr_role_id => $role) {
				$PHP_OUTPUT.= '<option value="' . $curr_role_id .'"';
				if ($curr_role_id == $role_id) $PHP_OUTPUT.= ' selected';
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
	$PHP_OUTPUT.= '</tr>';
}

$PHP_OUTPUT.= '</table>';
$PHP_OUTPUT.= '</div>';

if ($player->getAllianceID() == 0) {

	// Newbie alliance is unlimited members, * means no new members allowed
	if (($member_count < 30 || $alliance_id==302) && $password != '*') {
		$PHP_OUTPUT.= '<br>';
		$container = array();
		$container['url'] = 'alliance_join_processing.php';
		$container['alliance_id'] = $alliance_id;
		$form = create_form($container, 'Join');
		$PHP_OUTPUT.= $form['form'];
		$PHP_OUTPUT.= 'Enter password to join alliance<br><br>';
		$PHP_OUTPUT.= '<input type="password" name="password" size="30">&nbsp;';
		$PHP_OUTPUT.= $form['submit'];
		$PHP_OUTPUT.= '</form>';
	}

}

if ($alliance_id == $player->getAllianceID()) {
	$PHP_OUTPUT.= '<br><h2>Options</h2><br>';
	$container=array();
	$container['url'] = 'skeleton.php';
	$container['body'] = 'alliance_roster.php';
	if(!isset($varAction) || $varAction == 'Hide Alliance Roles') {
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

?>
