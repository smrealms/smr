<?
$message = $_REQUEST['message'];
if (sizeof($message) > 255)
	create_error('Not more than 255 characters per message!');

// put the msg into the database
$db->query('INSERT INTO announcement (time, admin_id, msg) VALUES('.TIME.', '.SmrSession::$account_id.', '.$db->escapeString($message).')');

forward(create_container('skeleton.php', 'game_play.php'))

?>