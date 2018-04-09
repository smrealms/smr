<?php

if(isset($_REQUEST['admin_id'])) {
	SmrSession::updateVar('admin_id',$_REQUEST['admin_id']);
}
if (isset($var['admin_id'])) {
	$admin_id = $var['admin_id'];
}

$template->assign('PageTopic','Manage Admin Permissions');

$PHP_OUTPUT.=('List of Accounts with Permissions:<br /><small>Click for details</small>');
$PHP_OUTPUT.=('<ul>');

$container = create_container('skeleton.php', 'permission_manage.php');

$db->query('SELECT account_id, login
			FROM account_has_permission JOIN account USING(account_id)
			GROUP BY account_id');
while ($db->nextRecord()) {
	$container['admin_id'] = $db->getField('account_id');
	$PHP_OUTPUT.=('<li>');
	$PHP_OUTPUT.=create_link($container, $db->getField('login'));
	$PHP_OUTPUT.=('</li>');
}

$PHP_OUTPUT.=('</ul>');

$PHP_OUTPUT.=('<p>&nbsp;</p>');

// if we don't have an account_id here
// we offer a list to choose
if (empty($admin_id)) {
	$PHP_OUTPUT.=('Select an Account to add Permissions:<br /><br />');

	$PHP_OUTPUT.=create_echo_form(create_container('skeleton.php', 'permission_manage.php'));
	$PHP_OUTPUT.=('<select name="admin_id">');
	$db->query('SELECT account_id, login
				FROM account
				WHERE validated = '.$db->escapeBoolean(true).'
				ORDER BY login');
	while ($db->nextRecord()) {
		$PHP_OUTPUT.=('<option value="' . $db->getField('account_id') . '">' . $db->getField('login') . '</option>');
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
			WHERE account_id = '.$db->escapeNumber($admin_id));
if (!$db->nextRecord()) {
	$PHP_OUTPUT.=('<u>Unknown Account</u>!');
	return;
}

$PHP_OUTPUT.=('<u>' . $db->getField('login') . '</u>!<br /><br />');

$container = create_container('permission_manage_processing.php', '');
$container['admin_id'] = $admin_id;

$PHP_OUTPUT.=create_echo_form($container);

// collect all the permission that this guy has
$db->query('SELECT permission_id
			FROM account_has_permission
			WHERE account_id = '.$db->escapeNumber($admin_id));
while ($db->nextRecord())
	$account_has_permissions[$db->getField('permission_id')] = true;

$db->query('SELECT permission_id, permission_name
			FROM permission');

$PHP_OUTPUT.=('<p style="padding-left:20px;">');
while ($db->nextRecord()) {
	if (isset($account_has_permissions[$db->getField('permission_id')]))
		$checked = ' checked';
	else
		$checked = '';
	$PHP_OUTPUT.=('<input type="checkbox" name="permission_ids[]" value="' . $db->getField('permission_id') . '"'.$checked.'>' . $db->getField('permission_name') . '<br />');
}
$PHP_OUTPUT.=('<br />');
$PHP_OUTPUT.=create_submit('Change');
$PHP_OUTPUT.=('&nbsp;&nbsp;&nbsp;');
$PHP_OUTPUT.=create_submit('Select Another User');
$PHP_OUTPUT.=('</p>');

$PHP_OUTPUT.=('</form>');
