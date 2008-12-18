<?

$value = strtoupper($_POST['action']);

$db->query('UPDATE player SET ignore_global = '.$db->escapeString($value).' WHERE game_id = '.$player->getGameID().' AND account_id = '.$player->getAccountID());

$container = array();
$container['url'] = 'skeleton.php';
$container['body'] = 'message_view.php';
forward($container);

?>