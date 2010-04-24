<?php
$id = $var["id"];
$action = $_REQUEST['action'];
if ($action == "Accept") {

	$db->query("INSERT INTO galactic_post_writer (account_id, game_id, position) VALUES ($id, $player->game_id, 'writer')");
    $player->send_message($id, MSG_PLAYER, format_string("You have been accepted as a <i>Galactic Post</i> writter.  Click the link on the left to start writting!", FALSE));
    $db->query("DELETE FROM galactic_post_applications WHERE account_id = $id AND game_id = $player->game_id");

} else {

	$db->query("DELETE FROM galactic_post_applications WHERE account_id = $id AND game_id = $player->game_id");
    $player->send_message($id, MSG_PLAYER, format_string("We are sorry to inform you that you were not accepted as a writter for the <i>Galactic Post</i>.", FALSE));

}
$container = array();
$container["url"] = "skeleton.php";
$container["body"] = "galactic_post_view_applications.php";
forward($container);

?>