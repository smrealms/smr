<?
$id = $var['id'];
$action = $_REQUEST['action'];
if ($action == 'Accept') {

	$db->query('INSERT INTO galactic_post_writer (account_id, game_id, position) VALUES ('.$id.', '.$player->getGameID().', \'writer\')');
    $player->sendMessage($id, MSG_PLAYER, $db->escape_string('You have been accepted as a <i>Galactic Post</i> writter.  Click the link on the left to start writing!', FALSE));
    $db->query('DELETE FROM galactic_post_applications WHERE account_id = '.$id.' AND game_id = '.$player->getGameID());

} else {

	$db->query('DELETE FROM galactic_post_applications WHERE account_id = '.$id.' AND game_id = '.$player->getGameID());
    $player->sendMessage($id, MSG_PLAYER, $db->escape_string('We are sorry to inform you that you were not accepted as a writer for the <i>Galactic Post</i>.', FALSE));

}
$container = array();
$container['url'] = 'skeleton.php';
$container['body'] = 'galactic_post_view_applications.php';
forward($container);

?>