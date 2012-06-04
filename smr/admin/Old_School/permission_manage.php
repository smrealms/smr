<?php

$admin_id = $_POST['admin_id'];
if (!empty($var['admin_id']))
	$admin_id = $var['admin_id'];

$smarty->assign('PageTopic','Manage Admin Permissions');

$PHP_OUTPUT.=('List of Accounts with Permissions:<br><small>Click for details</small>');
$PHP_OUTPUT.=('<ul>');

$container = create_container('skeleton.php', 'permission_manage.php');

$db->query('SELECT account_has_permission.account_id as admin_id, login
			FROM account_has_permission NATURAL JOIN account
			GROUP BY account_has_permission.account_id');
while ($db->next_record()) {

	$container['admin_id'] = $db->f('admin_id');
	$PHP_OUTPUT.=('<li>');
	$PHP_OUTPUT.=create_link($container, $db->f('login'));
	$PHP_OUTPUT.=('</li>');

}

$PHP_OUTPUT.=('</ul>');

$PHP_OUTPUT.=('<p>&nbsp;</p>');

// if we don't have an account_id here
// we offer a list to choose
if (empty($admin_id)) {

	$PHP_OUTPUT.=('Select an Account to add Permissions:<br><br>');

	$PHP_OUTPUT.=create_echo_form(create_container('skeleton.php', 'permission_manage.php'));
	$PHP_OUTPUT.=('<select name="admin_id">');
	$db->query('SELECT account_id, login
				FROM account
				ORDER BY login');
	while ($db->next_record()) {
		$PHP_OUTPUT.=('<option value="' . $db->f('account_id') . '">' . $db->f('login') . '</option>');
	}
	$PHP_OUTPUT.=('</select>');
	$PHP_OUTPUT.=('&nbsp;&nbsp;&nbsp;');
	$PHP_OUTPUT.=create_submit('Select');
	$PHP_OUTPUT.=('</form>');

	return;

}

$PHP_OUTPUT.=('Change permissions for the Account of ');

// get the account we got
$db->query('SELECT login
			FROM account
			WHERE account_id = '.$admin_id);
if (!$db->next_record()) {

	$PHP_OUTPUT.=('<u>Unknown Account</u>!');
	return;

}

$PHP_OUTPUT.=('<u>' . $db->f('login') . '</u>!<br><br>');

$container = create_container('permission_manage_processing.php', '');
$container['admin_id'] = $admin_id;

$PHP_OUTPUT.=create_echo_form($container);

$PHP_OUTPUT.=('<input type="hidden" name="admin_id" value="'.$admin_id.'">');

// collect all the permission that this guy has
$db->query('SELECT permission_id
			FROM account_has_permission
			WHERE account_id = '.$admin_id);
while ($db->next_record())
	$account_has_permissions[$db->f('permission_id')] = true;

$db->query('SELECT permission_id, permission_name
			FROM permission');

$PHP_OUTPUT.=('<p style="padding-left:20px;">');
while ($db->next_record()) {

	if (isset($account_has_permissions[$db->f('permission_id')]))
		$checked = ' checked';
	else
		$checked = '';
	$PHP_OUTPUT.=('<input type="checkbox" name="permission_ids[]" value="' . $db->f('permission_id') . '"'.$checked.'>' . $db->f('permission_name') . '<br>');
}
$PHP_OUTPUT.=('<br>');
$PHP_OUTPUT.=create_submit('Change');
$PHP_OUTPUT.=('&nbsp;&nbsp;&nbsp;');
$PHP_OUTPUT.=create_submit('Select Another User');
$PHP_OUTPUT.=('</p>');

$PHP_OUTPUT.=('</form>');

?>