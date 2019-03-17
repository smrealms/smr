<?php

$template->assign('PageTopic','Viewing Members');
Menu::galactic_post();

$container = array();
$container['url'] = 'skeleton.php';
$container['body'] = 'galactic_post_view_members.php';
if ($action == 'Remove')
	$db->query('DELETE FROM galactic_post_writer WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . ' AND account_id = '.$db->escapeNumber($var['id']));

$db->query('SELECT * FROM galactic_post_writer WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . ' AND account_id != ' . $db->escapeNumber($player->getAccountID()));
if ($db->getNumRows()) {

	$PHP_OUTPUT.=create_table();
	$PHP_OUTPUT.=('<tr>');
	$PHP_OUTPUT.=('<th>Player Name</th>');
	$PHP_OUTPUT.=('<th>Last Wrote</th>');
	$PHP_OUTPUT.=('<th>Options</th>');
	$PHP_OUTPUT.=('</tr>');

	while ($db->nextRecord()) {

		$curr_writter = SmrPlayer::getPlayer($db->getField('account_id'), $player->getGameID());
		$time = $db->getField('last_wrote');
		$PHP_OUTPUT.=('<tr>');
		$PHP_OUTPUT.=('<td class="center">'.$curr_writter->getPlayerName().'</td>');
		$PHP_OUTPUT.=('<td class="center"> ' . date(DATE_FULL_SHORT, $time) . '</td>');
		$container['id'] = $curr_writter->getAccountID();
		$PHP_OUTPUT.=create_echo_form($container);
		$PHP_OUTPUT.=('<td>');
		$PHP_OUTPUT.=create_submit('Remove');
		$PHP_OUTPUT.=('</td>');
		$PHP_OUTPUT.=('</tr>');
		$PHP_OUTPUT.=('</form>');

	}
	$PHP_OUTPUT.=('</table>');

}
