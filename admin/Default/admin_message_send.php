<?

$smarty->assign('PageTopic','Send Message');
$game_id = $_REQUEST['game_id'];
// check if we know the game yet
if (empty($game_id)) {

	$PHP_OUTPUT.=create_echo_form(create_container('skeleton.php', 'admin_message_send.php'));
	$PHP_OUTPUT.=('<p>Please select a game:</p>');
	$PHP_OUTPUT.=('<select name="game_id" size="1" id="InputFields">');
	$PHP_OUTPUT.=('<option value="20000">Send to All Players</option>');

	$db->query('SELECT * FROM game ORDER BY game_id');

	while ($db->nextRecord())
		$PHP_OUTPUT.=('<option value="' . $db->getField('game_id') . '">' . $db->getField('game_name') . '</option>');

	$PHP_OUTPUT.=('</select>&nbsp;&nbsp;');
	$PHP_OUTPUT.=create_submit('Select');
	$PHP_OUTPUT.=('</form>');

} else {

	$container = array();
	$container['url']		= 'admin_message_send_processing.php';
	$container['game_id']	= $game_id;

	$PHP_OUTPUT.=create_echo_form($container);
	if ($game_id != 20000) {
		
		$PHP_OUTPUT.=('<select name="account_id" size="1" id="InputFields">');
		$PHP_OUTPUT.=('<option value="0">[Please Select]</option>');
	
		$db->query('SELECT * FROM player WHERE game_id = '.$game_id.' ORDER BY player_id');
	
		while ($db->nextRecord())
			$PHP_OUTPUT.=('<option value="' . $db->getField('account_id') . '">' . stripslashes($db->getField('player_name')) . ' (' . $db->getField('player_id') . ')</option>');
	
		$PHP_OUTPUT.=('</select><br /><br />');
		
	}
	$PHP_OUTPUT.=('<textarea name="message" id="InputFields" style="width:350px;height:100px;"></textarea><br />');
	$PHP_OUTPUT.=('Hours Till Expire: <input type=text name=expire value=1 size=2 id=InputFields> (0 = never expire)<br /><br />');
	$PHP_OUTPUT.=create_submit('Send message');
	$PHP_OUTPUT.=('</form>');

}

?>