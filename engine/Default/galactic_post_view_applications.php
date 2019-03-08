<?php

$template->assign('PageTopic','Viewing Applications');
Menu::galactic_post();

$db->query('SELECT * FROM galactic_post_applications WHERE game_id = ' . $db->escapeNumber($player->getGameID()));
if ($db->getNumRows()) {
	$PHP_OUTPUT.=('You have received an application from the following players (click name to view description)<br />');
	$PHP_OUTPUT.=('Becareful when choosing your writters.  Make sure it is someone who will actually help you.<br /><br />');
}
else
	$PHP_OUTPUT.=('You have no applications to view at the current time.');
while ($db->nextRecord()) {
	$appliee = SmrPlayer::getPlayer($db->getField('account_id'), $player->getGameID());

	$container = array();
	$container['url'] = 'skeleton.php';
	$container['body'] = 'galactic_post_view_applications.php';
	$container['id'] = $appliee->getAccountID();
	$PHP_OUTPUT.=create_link($container, '<span class="yellow">'.$appliee->getPlayerName().'</span>');
	$PHP_OUTPUT.=(' who has ');
	if ($db->getField('written_before') == 'YES')
		$PHP_OUTPUT.=('written for some kind of a newspaper before.');
	else
		$PHP_OUTPUT.=('not written for a newspaper before.');
	$PHP_OUTPUT.=('<br />');
}
$PHP_OUTPUT.=('<br /><br />');
if (isset($var['id'])) {
	$db->query('SELECT * FROM galactic_post_applications WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . ' AND account_id = '.$db->escapeNumber($var['id']));
	$db->nextRecord();
	$desc = stripslashes($db->getField('description'));
	$applie = SmrPlayer::getPlayer($var['id'], $player->getGameID());
	$PHP_OUTPUT.=('Name : '.$applie->getPlayerName().'<br />');
	$PHP_OUTPUT.=('Have you written for some kind of newspaper before? ' . $db->getField('written_before'));
	$PHP_OUTPUT.=('<br />');
	$PHP_OUTPUT.=('How many articles are you willing to write per week? ' . $db->getField('articles_per_day'));
	$PHP_OUTPUT.=('<br />');
	$PHP_OUTPUT.=('What do you want to tell the editor?<br /><br />'.$desc);
	$container = array();
	$container['url'] = 'galactic_post_application_answer.php';
	transfer('id');
	$PHP_OUTPUT.=create_echo_form($container);
	$PHP_OUTPUT.=('<br /><br />');
	$PHP_OUTPUT.=create_submit('Accept');
	$PHP_OUTPUT.=create_submit('Reject');
	$PHP_OUTPUT.=('</form>');
}
