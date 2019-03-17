<?php

$template->assign('PageTopic','Reading The Wall');

Menu::bar();

$db->query('SELECT message_id FROM bar_wall WHERE sector_id = ' . $db->escapeNumber($sector->getSectorID()) . ' AND game_id = ' . $db->escapeNumber($player->getGameID()) . ' ORDER BY message_id DESC');
if ($db->nextRecord()) {
	$amount = $db->getInt('message_id') + 1;
}
else {
	$amount = 1;
}
$wall = $_REQUEST['wall'];
if (isset($wall)) {
	$db->query('INSERT INTO bar_wall (sector_id, game_id, message_id, message, time) VALUES (' . $db->escapeNumber($sector->getSectorID()) . ', ' . $db->escapeNumber($player->getGameID()) . ', ' . $db->escapeNumber($amount) . ',  ' . $db->escapeString($wall) . ' , ' . $db->escapeNumber(TIME) . ')');
}
$db->query('SELECT * FROM bar_wall WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . ' AND sector_id = ' . $db->escapeNumber($player->getSectorID()) . ' ORDER BY time DESC');
if ($db->getNumRows()) {
	$PHP_OUTPUT.=('<table class="standard">');
	$PHP_OUTPUT.=('<tr>');
	$PHP_OUTPUT.=('<th>Time written</th>');
	$PHP_OUTPUT.=('<th>Message</th>');
	$PHP_OUTPUT.=('</tr>');

	while ($db->nextRecord()) {
		$time = $db->getInt('time');
		$message_on_wall = $db->getField('message');

		$PHP_OUTPUT.=('<tr>');
		$PHP_OUTPUT.=('<td class="center"><b> ' . date(DATE_FULL_SHORT, $time) . ' </b></td>');
		$PHP_OUTPUT.=('<td class="center"><b>'.$message_on_wall.'</b></td>');
		$PHP_OUTPUT.=('</tr>');
	}
	$PHP_OUTPUT.=('</table>');
}
$template->assign('PageTopic','Write on the wall');

$PHP_OUTPUT.=('<br />');

$PHP_OUTPUT.=create_echo_form(create_container('skeleton.php', 'bar_read_wall.php'));
$PHP_OUTPUT.=('<textarea spellcheck="true" name="wall" class="InputFields"></textarea><br /><br />');
$PHP_OUTPUT.=create_submit('Write it');
$PHP_OUTPUT.=('</form>');
