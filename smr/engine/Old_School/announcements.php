<?

$smarty->assign('PageTopic','ANNOUNCEMENTS');

$PHP_OUTPUT.= '<table cellspacing="0" cellpadding="0" class="standard fullwidth">';
$PHP_OUTPUT.= '<tr><th>Time</th><th>Message</th></tr>';

if (!isset($var['view_all'])) {
	$db->query('SELECT
				announcement.time as time,
				account.login as login,
				announcement.msg as msg
				FROM announcement,account
				WHERE announcement.admin_id=account.account_id
				AND time > ' . $account->last_login . '
				ORDER BY time DESC'
				);
}
else {
	$db->query('SELECT
				announcement.time as time,
				account.login as login,
				announcement.msg as msg
				FROM announcement,account
				WHERE announcement.admin_id=account.account_id
				ORDER BY time DESC'
				);
}

while ($db->next_record()) {

	$PHP_OUTPUT.= '<tr>';
	$PHP_OUTPUT.= '<td class="shrink top nowrap">';
	//$PHP_OUTPUT.=  $db->f('login');
	//$PHP_OUTPUT.= '<br>';
	$PHP_OUTPUT.= date('n/j/Y\<b\r /\>g:i:s A', $db->f('time'));
	$PHP_OUTPUT.= '</td><td class="top">';
	$PHP_OUTPUT.= $db->f('msg');
	$PHP_OUTPUT.= '</td></tr>';
}

$PHP_OUTPUT.= '</table><br>';

$container = array();
$container['url'] = 'logged_in.php';
$container['body'] = '';
$PHP_OUTPUT.=create_button($container,'Select a Game!');

?>