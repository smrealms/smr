<?php

$template->assign('PageTopic','Viewing Members');
require_once(get_file_loc('menu.inc'));
create_galactic_post_menue();

$container = array();
$container['url'] = 'skeleton.php';
$container['body'] = 'galactic_post_view_members.php';
if ($action == 'Remove')
	$db->query('DELETE FROM galactic_post_writer WHERE game_id = '.$player->getGameID().' AND account_id = '.$var['id']);

$db->query('SELECT * FROM galactic_post_writer WHERE game_id = '.$player->getGameID().' AND account_id != '.$player->getAccountID());
if ($db->getNumRows()) {

	$PHP_OUTPUT.=create_table();
	$PHP_OUTPUT.=('<tr>');
	$PHP_OUTPUT.=('<th align="center">Player Name</th>');
	$PHP_OUTPUT.=('<th align="center">Last Wrote</th>');
    $PHP_OUTPUT.=('<th align="center">Options</th>');
	$PHP_OUTPUT.=('</tr>');

    while ($db->nextRecord()) {

	    $curr_writter =& SmrPlayer::getPlayer($db->getField('account_id'), $player->getGameID());
    	$time = $db->getField('last_wrote');
        $PHP_OUTPUT.=('<tr>');
	    $PHP_OUTPUT.=('<td align="center">'.$curr_writter->getPlayerName().'</td>');
    	$PHP_OUTPUT.=('<td align="center"> ' . date(DATE_FULL_SHORT, $time) . '</td>');
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
?>