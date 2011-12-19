<?php

$template->assign('PageTopic','Announcements');

$PHP_OUTPUT.= '<table class="standard fullwidth">';
$PHP_OUTPUT.= '<tr><th>Time</th><th>Message</th></tr>';

if (!isset($var['view_all'])) {
	$db->query('SELECT time, login, msg
				FROM announcement
				JOIN account ON announcement.admin_id=account.account_id
				AND time > ' . $account->getLastLogin() . '
				ORDER BY time DESC');
}
else {
	$db->query('SELECT time, login, msg
				FROM announcement
				JOIN account ON announcement.admin_id=account.account_id
				ORDER BY time DESC');
}

while ($db->nextRecord()) {
	$PHP_OUTPUT.= '<tr>';
	$PHP_OUTPUT.= '<td class="shrink top noWrap">';
	//$PHP_OUTPUT.= $db->getField('login');
	//$PHP_OUTPUT.= '<br />';
	$PHP_OUTPUT.= date(DATE_FULL_SHORT_SPLIT, $db->getField('time'));
	$PHP_OUTPUT.= '</td><td class="top">';
	$PHP_OUTPUT.= bbifyMessage($db->getField('msg'));
	$PHP_OUTPUT.= '</td></tr>';
}

$PHP_OUTPUT.= '</table><br />';

$container = create_container('logged_in.php');
$PHP_OUTPUT.=create_button($container,'Select a Game!');

?>