<?

$smarty->assign('PageTopic','Delete Game - Confirmation');
$game_id = $_REQUEST['game_id'];
$db->query('SELECT game_name, end_date
			FROM game
			WHERE game_id = '.$game_id);
if ($db->next_record()) {

	$name		= $db->f('game_name');
	$end_date	= $db->f('end_date');

	$PHP_OUTPUT.=('Are you sure you want to delete <i>'.$name.'?</i><br>');
	if (date('Y-m-d') < $end_date)
		$PHP_OUTPUT.=('<span style="color:red;"><b>WARNING!</b> This game hasn\'t ended yet!</span><br>');
	$PHP_OUTPUT.=('<br>');

	$container = array();
	$container['url'] = 'game_delete_processing.php';
	$container['game_id'] = $game_id;

	$PHP_OUTPUT.=create_echo_form($container);
	$PHP_OUTPUT.=('Do you want to save the game to the history DB?<br>');
	$PHP_OUTPUT.=('Yes:<input type=radio name=save value="Yes"><br>');
	$PHP_OUTPUT.=('No:<input type=radio name=save value="No"><p>');
	$PHP_OUTPUT.=create_submit('Yes');
	$PHP_OUTPUT.=('&nbsp;&nbsp;');
	$PHP_OUTPUT.=create_submit('No');
	$PHP_OUTPUT.=('</form>');

}

?>