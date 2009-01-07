<?

//we are closing
$action = $_REQUEST['action'];
if ($action == 'Close') {

	$db->query('REPLACE INTO game_disable (reason) VALUES (' . $db->escape_string($close_reason, true) . ')');
	$db->query('DELETE FROM active_session');

} elseif ($action == 'Open')
	$db->query('DELETE FROM game_disable');

$container = array();
$container['url'] = 'skeleton.php';
$container['body'] = 'game_play.php';
forward($container);

?>