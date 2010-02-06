<?php
if (isset($var['alliance_id'])) $alliance_id = $var['alliance_id'];
else $alliance_id = $player->getAllianceID();
$db->query('SELECT leader_id,alliance_id,alliance_name FROM alliance WHERE game_id=' . SmrSession::$game_id . ' AND alliance_id=' . $alliance_id . ' LIMIT 1');
$db->nextRecord();
$template->assign('PageTopic',stripslashes($db->getField('alliance_name')) . ' (' . $db->getField('alliance_id') . ')');
include(get_file_loc('menue.inc'));
create_alliance_menue($alliance_id,$db->getField('leader_id'));

$PHP_OUTPUT.= '<h2>Current Roles</h2><br />';

$db->query('SELECT * 
FROM alliance_has_roles
WHERE game_id=' . $player->getGameID() . '
AND alliance_id=' . $alliance_id .'
ORDER BY role_id
');

while ($db->nextRecord()) {
	$role_id = $db->getField('role_id');
	if ($var['role_id'] == $role_id) {
		$container = array();
		$container['url'] = 'alliance_roles_processing.php';
		$container['body'] = '';
		$container['role_id'] = $var['role_id'];
		$container['alliance_id'] = $alliance_id;
		/*$db->query('SELECT * FROM alliance_has_roles WHERE game_id = '.$player->getGameID().' AND alliance_id = '.$player->getAllianceID().' AND ' . 
					'role_id = ' . $var['role_id']);
		$db->nextRecord();*/
		$form = create_form($container,'Submit Changes');
	
		$PHP_OUTPUT.= $form['form'];
		$PHP_OUTPUT.=create_table();
		$PHP_OUTPUT.= '<tr><td align="left">Name</td><td><input type="text" name="role" value="' . stripslashes($db->getField('role')) . '" maxlength="32"></tr>';
		$PHP_OUTPUT.= '<tr><td align="left" rowspan="3">Max withdrawl per 24 hours</td><td align="left"><input type="text" name="maxWith" value="';
		if ($db->getField('with_per_day') > 0) $PHP_OUTPUT.= $db->getField('with_per_day');
		else $PHP_OUTPUT.= '0';
		$PHP_OUTPUT.= '" maxlength="32"></td>';
		$PHP_OUTPUT.= '<tr><td align="left">Unlimited:<input type="checkbox" name="unlimited"';
		if ($db->getField('with_per_day') == -2) $PHP_OUTPUT.= ' checked';
		$PHP_OUTPUT.= '></td>';
		$PHP_OUTPUT.= '<tr><td align="left">Positive Balance:<input type="checkbox" name="positive" alt="Memebers must deposit more than they withdrawl."';
		if ($db->getField('with_per_day') == -1) $PHP_OUTPUT.= ' checked';
		$PHP_OUTPUT.= '></td></tr>';
		if ($db->getField('treaty_created') == 'FALSE') {
			$PHP_OUTPUT.= '<tr><td align="left">Remove Member</td><td align="left"><input type="checkbox" name="removeMember"';
			if ($db->getField('remove_member') == 'TRUE') $PHP_OUTPUT.= ' checked';
			$PHP_OUTPUT.= '></td></tr>';
			$PHP_OUTPUT.= '<tr><td align="left">Change Password</td><td align="left"><input type="checkbox" name="changePW"';
			if ($db->getField('change_pass') == 'TRUE') $PHP_OUTPUT.= ' checked';
			$PHP_OUTPUT.= '></td></tr>';
			$PHP_OUTPUT.= '<tr><td align="left">Change MoD</td><td align="left"><input type="checkbox" name="changeMoD"';
			if ($db->getField('change_mod') == 'TRUE') $PHP_OUTPUT.= ' checked';
			$PHP_OUTPUT.= '></td></tr>';
			$PHP_OUTPUT.= '<tr><td align="left">Change Roles</td><td align="left"><input type="checkbox" name="changeRoles"';
			if ($db->getField('change_roles') == 'TRUE') $PHP_OUTPUT.= ' checked';
			$PHP_OUTPUT.= '></td></tr>';
			$PHP_OUTPUT.= '<tr><td align="left">Land On Planets</td><td align="left"><input type="checkbox" name="planets"';
			if ($db->getField('planet_access') == 'TRUE') $PHP_OUTPUT.= ' checked';
			$PHP_OUTPUT.= '></td></tr>';
			$PHP_OUTPUT.= '<tr><td align="left">Delete messageboard messages</td><td align="left"><input type="checkbox" name="mbMessages"';
			if ($db->getField('mb_messages') == 'TRUE') $PHP_OUTPUT.= ' checked';
			$PHP_OUTPUT.= '></td></tr>';
			$PHP_OUTPUT.= '<tr><td align="left">Make withdrawls exempt</td><td align="left"><input type="checkbox" name="exemptWithdrawls" alt="This user can mark withdrawls from the alliance account as \'for the alliance\' instead of \'for the individual\'"';
			if ($db->getField('exempt_with') == 'TRUE') $PHP_OUTPUT.= ' checked';
			$PHP_OUTPUT.= '></td></tr>';
			$PHP_OUTPUT.= '<tr><td align="left">Send Alliance Message</td><td align="left"><input type="checkbox" name="sendAllMsg"';
			if ($db->getField('send_alliance_msg') == 'TRUE') $PHP_OUTPUT.= ' checked';
			$PHP_OUTPUT.= '></td></tr>';
		}
		$PHP_OUTPUT.= '<tr><td colspan="2" align="center">' . $form['submit'] . '</td></tr>';
		$PHP_OUTPUT.= '</table></form><br />';
	}
	else
	{
		$container = array();
		$container['url'] = 'skeleton.php';
		$container['body'] = 'alliance_roles.php';
		$container['role_id'] = $db->getField('role_id');
		$container['alliance_id'] = $alliance_id;
		$form = create_form($container,'Edit');
		$PHP_OUTPUT.= $form['form'];
		$PHP_OUTPUT.= '<input type="text" name="role" value="' . stripslashes($db->getField('role')) . '" maxlength="32">&nbsp;&nbsp;';
		$PHP_OUTPUT.= $form['submit'];
		$PHP_OUTPUT.= '</form><br />';
	}
}
$PHP_OUTPUT.= '<h2>Create Role</h2><br />';

$container = array();
$container['url'] = 'alliance_roles_processing.php';
$container['alliance_id'] = $alliance_id;
$container['body'] = '';
$form = create_form($container,'Create');

$PHP_OUTPUT.= $form['form'];
$PHP_OUTPUT.=create_table();
$PHP_OUTPUT.= '<tr><td align="left">Name</td><td><input type="text" name="role" value="" maxlength="32"></td></tr>';
$PHP_OUTPUT.= '<tr><td align="left" rowspan="3">Max withdrawl per 24 hours</td><td align="left"><input type="text" name="maxWith" value="0" maxlength="32"></td>';
$PHP_OUTPUT.= '<tr><td align="left">Unlimited:<input type="checkbox" name="unlimited"></td>';
$PHP_OUTPUT.= '<tr><td align="left">Positive Balance:<input type="checkbox" name="positive" alt="Memebers must deposit more than they withdrawl."></td></tr>';
$PHP_OUTPUT.= '<tr><td align="left">Remove Member</td><td align="left"><input type="checkbox" name="removeMember"></td></tr>';
$PHP_OUTPUT.= '<tr><td align="left">Change Password</td><td align="left"><input type="checkbox" name="changePW"></td></tr>';
$PHP_OUTPUT.= '<tr><td align="left">Change MoD</td><td align="left"><input type="checkbox" name="changeMoD"></td></tr>';
$PHP_OUTPUT.= '<tr><td align="left">Change Roles</td><td align="left"><input type="checkbox" name="changeRoles"></td></tr>';
$PHP_OUTPUT.= '<tr><td align="left">Land On Planets</td><td align="left"><input type="checkbox" name="planets" checked></td></tr>';
$PHP_OUTPUT.= '<tr><td align="left">Delete messageboard messages</td><td align="left"><input type="checkbox" name="mbMessages"></td></tr>';
$PHP_OUTPUT.= '<tr><td align="left">Make withdrawls exempt</td><td align="left"><input type="checkbox" name="exemptWithdrawls" alt="This user can mark withdrawls from the alliance account as \'for the alliance\' instead of \'for the individual\'"></td></tr>';
$PHP_OUTPUT.= '<tr><td align="left">Send Alliance Message</td><td align="left"><input type="checkbox" name="sendAllMsg"></td></tr>';
$PHP_OUTPUT.= '<tr><td colspan="2" align="center">' . $form['submit'] . '</td></tr>';
$PHP_OUTPUT.= '</table></form><br />';

$PHP_OUTPUT.= '<b>Usage:</b><br />To add a new entry input the name of the role in the name field and press \'Create\'.<br />To delete an entry clear the box and click \'Edit\'.';

?>