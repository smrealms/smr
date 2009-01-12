<?php
if (isset($var['alliance_id'])) $alliance_id = $var['alliance_id'];
else $alliance_id = $player->alliance_id;
$db->query('SELECT leader_id,alliance_id,alliance_name FROM alliance WHERE game_id=' . $session->game_id . ' AND alliance_id=' . $alliance_id . ' LIMIT 1');
$db->next_record();
print_topic(stripslashes($db->f("alliance_name")) . ' (' . $db->f("alliance_id") . ')');
include(get_file_loc('menue.inc'));
print_alliance_menue($alliance_id,$db->f('leader_id'));

echo '<h2>Current Roles</h2><br>';

$db->query('SELECT * 
FROM alliance_has_roles
WHERE game_id=' . $player->game_id . '
AND alliance_id=' . $alliance_id .'
ORDER BY role_id
');

while ($db->next_record()) {
	$role_id = $db->f("role_id");
	if ($var['role_id'] == $role_id) {
		$container = array();
		$container['url'] = 'alliance_roles_processing.php';
		$container['body'] = '';
		$container['role_id'] = $var['role_id'];
		$container['alliance_id'] = $alliance_id;
		/*$db->query("SELECT * FROM alliance_has_roles WHERE game_id = $player->game_id AND alliance_id = $player->alliance_id AND " . 
					"role_id = " . $var['role_id']);
		$db->next_record();*/
		$form = create_form($container,'Submit Changes');
	
		echo $form['form'];
		print_table();
		echo '<tr><td align="left">Name</td><td><input type="text" name="role" value="' . stripslashes($db->f("role")) . '" maxlength="32"></tr>';
		echo '<tr><td align="left" rowspan="3">Max withdrawl per 24 hours</td><td align="left"><input type="text" name="maxWith" value="';
		if ($db->f("with_per_day") > 0) echo $db->f("with_per_day");
		else echo '0';
		echo '" maxlength="32"></td>';
		echo '<tr><td align="left">Unlimited:<input type="checkbox" name="unlimited"';
		if ($db->f("with_per_day") == -2) echo ' checked';
		echo '></td>';
		echo '<tr><td align="left">Positive Balance:<input type="checkbox" name="positive" alt="Memebers must deposit more than they withdrawl."';
		if ($db->f("with_per_day") == -1) echo ' checked';
		echo '></td></tr>';
		if (!$db->f("treaty_created")) {
			echo '<tr><td align="left">Remove Member</td><td align="left"><input type="checkbox" name="removeMember"';
			if ($db->f("remove_member")) echo ' checked';
			echo '></td></tr>';
			echo '<tr><td align="left">Change Password</td><td align="left"><input type="checkbox" name="changePW"';
			if ($db->f("change_pass")) echo ' checked';
			echo '></td></tr>';
			echo '<tr><td align="left">Change MoD</td><td align="left"><input type="checkbox" name="changeMoD"';
			if ($db->f("change_mod")) echo ' checked';
			echo '></td></tr>';
			echo '<tr><td align="left">Change Roles</td><td align="left"><input type="checkbox" name="changeRoles"';
			if ($db->f("change_roles")) echo ' checked';
			echo '></td></tr>';
			echo '<tr><td align="left">Land On Planets</td><td align="left"><input type="checkbox" name="planets"';
			if ($db->f("planet_access")) echo ' checked';
			echo '></td></tr>';
			echo '<tr><td align="left">Delete messageboard messages</td><td align="left"><input type="checkbox" name="mbMessages"';
			if ($db->f("mb_messages")) echo ' checked';
			echo '></td></tr>';
			echo '<tr><td align="left">Make withdrawls exempt</td><td align="left"><input type="checkbox" name="exemptWithdrawls" alt="This user can mark withdrawls from the alliance account as \'for the alliance\' instead of \'for the individual\'"';
			if ($db->f("exempt_with")) echo ' checked';
			echo '></td></tr>';
			echo '<tr><td align="left">Send Alliance Message</td><td align="lefT"><input type="checkbox" name="sendAllMsg"';
			if ($db->f("send_alliance_msg")) echo ' checked';
			echo '></td></tr>';
		}
		echo '<tr><td colspan="2" align="center">' . $form['submit'] . '</td></tr>';
		echo '</table></form><br />';
	} else {
		$container = array();
		$container['url'] = 'skeleton.php';
		$container['body'] = 'alliance_roles.php';
		$container['role_id'] = $db->f('role_id');
		$container['alliance_id'] = $alliance_id;
		$form = create_form($container,'Edit');
		echo $form['form'];
		echo '<input type="text" name="role" value="' . stripslashes($db->f('role')) . '" maxlength="32">&nbsp;&nbsp;';
		echo $form['submit'];
		echo '</form><br>';
	}
}
echo '<h2>Create Role</h2><br />';

$container = array();
$container['url'] = 'alliance_roles_processing.php';
$container['alliance_id'] = $alliance_id;
$container['body'] = '';
$form = create_form($container,'Create');

echo $form['form'];
print_table();
echo '<tr><td align="left">Name</td><td><input type="text" name="role" value="" maxlength="32"></td></tr>';
echo '<tr><td align="left" rowspan="3">Max withdrawl per 24 hours</td><td align="left"><input type="text" name="maxWith" value="0" maxlength="32"></td>';
echo '<tr><td align="left">Unlimited:<input type="checkbox" name="unlimited"></td>';
echo '<tr><td align="left">Positive Balance:<input type="checkbox" name="positive" alt="Memebers must deposit more than they withdrawl."></td></tr>';
echo '<tr><td align="left">Remove Member</td><td align="left"><input type="checkbox" name="removeMember"></td></tr>';
echo '<tr><td align="left">Change Password</td><td align="left"><input type="checkbox" name="changePW"></td></tr>';
echo '<tr><td align="left">Change MoD</td><td align="left"><input type="checkbox" name="changeMoD"></td></tr>';
echo '<tr><td align="left">Change Roles</td><td align="left"><input type="checkbox" name="changeRoles"></td></tr>';
echo '<tr><td align="left">Land On Planets</td><td align="left"><input type="checkbox" name="planets" checked></td></tr>';
echo '<tr><td align="left">Delete messageboard messages</td><td align="left"><input type="checkbox" name="mbMessages"></td></tr>';
echo '<tr><td align="left">Make withdrawls exempt</td><td align="left"><input type="checkbox" name="exemptWithdrawls" alt="This user can mark withdrawls from the alliance account as \'for the alliance\' instead of \'for the individual\'"></td></tr>';
echo '<tr><td align="left">Send Alliance Message</td><td align="lefT"><input type="checkbox" name="sendAllMsg"></td></tr>';
echo '<tr><td colspan="2" align="center">' . $form['submit'] . '</td></tr>';
echo '</table></form><br />';

echo '<b>Usage:</b><br>To add a new entry input the name of the role in the name field and press \'Create\'.<br />To delete an entry clear the box and click \'Edit\'.';

?>