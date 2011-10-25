<?php
$sector =& $player->getSector();

$template->assign('PageTopic','Reading The Wall');

include(get_file_loc('menue.inc'));
$PHP_OUTPUT.=create_bar_menue();

$db = new SmrMySqlDatabase();
$db->query('SELECT message_id FROM bar_wall WHERE sector_id = '.$sector->getSectorID().' AND game_id = '.SmrSession::$game_id.' ORDER BY message_id DESC');
if ($db->nextRecord())
	$amount = $db->getField('message_id') + 1;
else
	$amount = 1;
$time_now = TIME;
$db2 = new SmrMySqlDatabase();
$wall = $_REQUEST['wall'];
if (isset($wall))
	$db2->query('INSERT INTO bar_wall (sector_id, game_id, message_id, message, time) VALUES ('.$sector->getSectorID().', '.SmrSession::$game_id.', '.$amount.',  ' . $db->escape_string($wall, true) . ' , '.$time_now.')');
$db->query('SELECT * FROM bar_wall WHERE game_id = '.$player->getGameID().' AND sector_id = '.$player->getSectorID().' ORDER BY time DESC');
if ($db->getNumRows()) {

	$PHP_OUTPUT.=('<table class="standard">');
	$PHP_OUTPUT.=('<tr>');
	$PHP_OUTPUT.=('<th align="center">Time written</th>');
	$PHP_OUTPUT.=('<th align="center">Message</th>');
	$PHP_OUTPUT.=('</tr>');

	while ($db->nextRecord()) {

		$time = $db->getField('time');
		$message_on_wall = stripslashes($db->getField('message'));

		$PHP_OUTPUT.=('<tr>');
		$PHP_OUTPUT.=('<td align="center"><b> ' . date(DATE_FULL_SHORT, $time) . ' </b></td>');
		$PHP_OUTPUT.=('<td align="center"><b>'.$message_on_wall.'</b></td>');
		$PHP_OUTPUT.=('</tr>');

	}
    $PHP_OUTPUT.=('</table>');
}
$template->assign('PageTopic','Write on the wall');

$PHP_OUTPUT.=('<br />');

$PHP_OUTPUT.=create_echo_form(create_container('skeleton.php', 'bar_read_wall.php'));
$PHP_OUTPUT.=('<textarea name="wall" id="InputFieldsText"></textarea><br /><br />');
$PHP_OUTPUT.=create_submit('Write it');
$PHP_OUTPUT.=('</form>');

?>