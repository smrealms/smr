<?

$smarty->assign('PageTopic','Show Map');
$game_id = $_REQUEST['game_id'];
// check if we know the game yet
if (empty($game_id)) {

	$PHP_OUTPUT.=create_echo_form(create_container('skeleton.php', 'map_show.php'));
	$PHP_OUTPUT.=('<p>Please select a game:</p>');
	$PHP_OUTPUT.=('<select name="game_id" size="1" id="InputFields">');
	$PHP_OUTPUT.=('<option value="0">[Please Select]</option>');

	$db->query('SELECT * FROM game ORDER BY game_id');

	while ($db->next_record())
		$PHP_OUTPUT.=('<option value="' . $db->f('game_id') . '">' . $db->f('game_name') . '</option>');

	$PHP_OUTPUT.=('</select>&nbsp;&nbsp;');
	$PHP_OUTPUT.=create_submit('Select');
	$PHP_OUTPUT.=('</form>');

} else {

	$container = array();
	$container['url']		= 'map_show_processing.php';
	$container['game_id']	= $game_id;

	$PHP_OUTPUT.=create_echo_form($container);
	$PHP_OUTPUT.=('<select name="account_id" size="1" id="InputFields">');
	$PHP_OUTPUT.=('<option value="0">[Please Select]</option>');

	$db->query('SELECT * FROM player WHERE game_id = '.$game_id.' ORDER BY player_id');

	while ($db->next_record())
		$PHP_OUTPUT.=('<option value="' . $db->f('account_id') . '">' . stripslashes($db->f('player_name')) . ' (' . $db->f('player_id') . ')</option>');

	$PHP_OUTPUT.=('</select>&nbsp;&nbsp;');
	$PHP_OUTPUT.=create_submit('Reveal Map');
	$PHP_OUTPUT.=('</form>');

}

?>