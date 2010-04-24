<?php

print_topic("ANNOUNCEMENTS");

echo '<table cellspacing="0" cellpadding="0" class="standard fullwidth">';
echo '<tr><th>Time</th><th>Message</th></tr>';

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

	echo '<tr>';
	echo '<td class="shrink top nowrap">';
	//echo  $db->f('login');
	//echo '<br>';
	echo date('n/j/Y\<b\r /\>g:i:s A', $db->f('time'));
	echo '</td><td class="top">';
	echo $db->f('msg');
	echo '</td></tr>';
}

echo '</table><br>';

$container = array();
$container['url'] = 'logged_in.php';
$container['body'] = '';
print_button($container,'Select a Game!');

?>