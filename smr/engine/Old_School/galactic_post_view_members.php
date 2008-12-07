<?

$smarty->assign('PageTopic','VIEWING MEMBERS');
include($ENGINE . 'global/menue.inc');
$PHP_OUTPUT.=create_galactic_post_menue();

$container = array();
$container['url'] = 'skeleton.php';
$container['body'] = 'galactic_post_view_members.php';
if ($action == 'Remove')
	$db->query('DELETE FROM galactic_post_writer WHERE game_id = '.$player->getGameID().' AND account_id = '.$var['id']);

$db->query('SELECT * FROM galactic_post_writer WHERE game_id = '.$player->getGameID().' AND account_id != '.$player->getAccountID());
if ($db->nf()) {

	$PHP_OUTPUT.=create_table();
	$PHP_OUTPUT.=('<tr>');
	$PHP_OUTPUT.=('<th align="center">Player Name</th>');
	$PHP_OUTPUT.=('<th align="center">Last Wrote</th>');
    $PHP_OUTPUT.=('<th align="center">Options</th>');
	$PHP_OUTPUT.=('</tr>');

    while ($db->next_record()) {

	    $curr_writter =& SmrPlayer::getPlayer($db->f('account_id'), $player->getGameID());
    	$time = $db->f('last_wrote');
        $PHP_OUTPUT.=('<tr>');
	    $PHP_OUTPUT.=('<td align="center">'.$curr_writter->player_name.'</td>');
    	$PHP_OUTPUT.=('<td align="center"> ' . date('n/j/Y g:i:s A', $time) . '</td>');
	    $container['id'] = $curr_writter->account_id;
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