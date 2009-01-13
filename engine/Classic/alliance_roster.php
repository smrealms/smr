<?

if (isset($var["alliance_id"])) {
	$alliance_id=$var['alliance_id'];
}
else {
	$alliance_id=$player->alliance_id;
}

$db->query('SELECT alliance_name,leader_id,alliance_password FROM alliance WHERE game_id=' . SmrSession::$game_id . ' AND alliance_id=' . $alliance_id . ' LIMIT 1');
$db->next_record();
print_topic(stripslashes($db->f('alliance_name')) . ' (' . $alliance_id . ')');
include(get_file_loc('menue.inc'));
print_alliance_menue($alliance_id,$db->f('leader_id'));

$leader_id = $db->f('leader_id');
$password = $db->f('alliance_password');

$db2 = new SmrMySqlDatabase();

// Does anyone actually use these?
if ($var['action'] == 'Show Alliance Roles') {
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
if($alliance_id == $player->alliance_id) {
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
		sum(player_cache.experience) as alliance_xp,
		floor(avg(player_cache.experience)) as alliance_avg,
		alliance.alliance_name as alliance_name,
		player.alliance_id as alliance_id 
		FROM player,alliance,player_cache
		WHERE player.alliance_id=' . $alliance_id  . '
		AND alliance.alliance_id=' . $alliance_id  . '
		AND player.game_id = ' . SmrSession::$game_id . '
		AND alliance.game_id = ' . SmrSession::$game_id . '
		AND player_cache.game_id = ' . SmrSession::$game_id . '
		AND player_cache.account_id = player.account_id
		GROUP BY alliance.alliance_id'
	);
}

$db->next_record();

$member_count = $db->f('alliance_member_count');
echo $form['form'];
echo '<div align="center">';
echo $db->f('description');
echo '<br><br>';

echo '
<table cellspacing="0" cellpadding="0" class="standard inset">
	<tr>
		<th>Alliance Name</th>
		<th>Total Experience</th>
		<th>Average Experience</th>
		<th>Members</th>
	</tr>
	<tr class="bold">
		<td>';
echo stripslashes($db->f('alliance_name'));
echo '
		</td>
		<td class="center shrink">';
echo $db->f('alliance_xp');
echo '
		</td>
		<td class="center shrink">';
echo $db->f('alliance_avg');
echo '
		</td>
		<td class="center shrink">';
echo $db->f('alliance_member_count');
echo '
		</td>';
echo '
	</tr>
</table>';

echo '</div><br>';

echo '<h2>Current Members</h2><br>';

echo '<div align="center">';

echo '
<table cellspacing="0" cellpadding="0" class="standard fullwidth">
	<tr>
	<th>&nbsp;</th>
	<th>Trader Name</th>
	<th>Race</th>
	<th>Experience</th>
';

if($var['action'] == 'Show Alliance Roles') {
	echo '<th>Role</th>';
}

echo '</tr>';
if($alliance_id == $player->alliance_id) {
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
		SELECT player.account_id as account_id,' .
		'player.player_name as player_name,' .
		'player.player_id as player_id,' .
		'player_cache.experience as experience,' .
		'player.alignment as alignment,' .
		'player.race_id as race_id ' .
		'FROM player,player_cache ' .
		'WHERE ' .
		'player.game_id = ' . SmrSession::$game_id . ' ' .
		'AND player.game_id = player_cache.game_id ' .
		'AND player.account_id = player_cache.account_id ' .
		'AND alliance_id=' . $alliance_id . ' ' .
		'ORDER BY experience DESC'
	);
}
$count = 1;

$container=array();
$container['url']= 'skeleton.php';
$db2->query("SELECT * FROM player_has_alliance_role WHERE account_id = $player->account_id AND game_id = $player->game_id");
if ($db2->next_record()) $my_role_id = $db2->f("role_id");
else $my_role_id = 0;
$db2->query("SELECT * FROM alliance_has_roles WHERE alliance_id = $player->alliance_id AND game_id = $player->game_id AND " . 
					"role_id = $my_role_id AND change_roles = 1");
if ($db2->next_record()) $allowed = TRUE;
while ($db->next_record()) {
	// check if this guy is the current guy
	if ( $db->f("account_id") == SmrSession::$account_id)
		echo '<tr class="bold">';
	else
		echo '<tr>';

	echo '<td class="center shrink">';

	// counter
	if ($db->f("account_id") == $leader_id) echo '*';
	echo ($count++);
	echo '</td><td>';

	// player name
	$db2->query('SELECT level_name
				FROM level
				WHERE requirement<=' . $db->f('experience') . '
				ORDER BY requirement DESC LIMIT 1'
				);
	$db2->next_record();

	echo $db2->f('level_name');
	echo '&nbsp;';
	$container['body'] = 'trader_search_result.php';
	$container["player_id"] = $db->f('player_id');
	print_link($container, get_colored_text($db->f("alignment"),stripslashes($db->f('player_name')) . '&nbsp;(' . $db->f('player_id') . ')'));
	echo '</td><td class="center shrink">';

	// race name (colored)
	$container['body'] = 'council_list.php';
	$container["race_id"]	= $db->f("race_id");
	$container["race_name"]	= $races[$db->f("race_id")];
	unset($container['player_id']);

	print_link($container, $player->get_colored_race($db->f("race_id")));

	// xp
	echo '</td><td class="shrink center">';
	echo $db->f('experience');
	echo '</td>';

	// Roles
	if ($var['action'] == 'Show Alliance Roles') {

		echo '<td class="shrink right">';

		$db2 = new SmrMySqlDatabase();
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

		if ($allowed && $db->f("account_id") != $leader_id) {
		// ok do we display a select box or just a plain entry
		/*if (SmrSession::$account_id == $db->f('account_id') ||
			SmrSession::$account_id == $leader_id) {*/

			echo '<select name="role[' . $db->f('account_id') . ']" id="InputFields">';
			foreach ($roles as $curr_role_id => $role) {
				echo '<option value="' . $curr_role_id .'"';
				if ($curr_role_id == $role_id) echo ' selected';
				echo '>';
				echo stripslashes($role);
				echo '</option>';
			}
			echo '</select>';

		}
		else {
			echo $roles[$role_id];
		}
		echo '</td>';
	}
	echo '</tr>';
}

echo '</table>';
echo '</div>';

if ($player->alliance_id == 0) {

	// Newbie alliance is unlimited members, * means no new members allowed
	if (($member_count < 30 || $alliance_id==302) && $password != "*") {
		echo '<br>';
		$container = array();
		$container["url"] = "alliance_join_processing.php";
		$container["alliance_id"] = $alliance_id;
		$form = create_form($container, 'Join');
		echo $form['form'];
		echo 'Enter password to join alliance<br><br>';
		echo '<input type="password" name="password" size="30">&nbsp;';
		echo $form['submit'];
		echo '</form>';
	}

}

if ($alliance_id == $player->alliance_id) {
	echo '<br><h2>Options</h2><br>';
	$container=array();
	$container['url'] = 'skeleton.php';
	$container['body'] = 'alliance_roster.php';
	if(!isset($var['action']) || $var['action'] == 'Hide Alliance Roles') {
		$container['action'] = 'Show Alliance Roles';
		print_button($container,'Show Alliance Roles');
	}
	else {
		echo $form['submit'];
		echo '&nbsp;&nbsp;';
		$container['action'] = 'Hide Alliance Roles';
		print_button($container,'Hide Alliance Roles');
		echo '</form>';
	}
}

?>
