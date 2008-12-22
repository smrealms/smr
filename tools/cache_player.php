<?php


require_once ( realpath(dirname(__FILE__)) . '/../htdocs/config.inc');
require_once ($INCLUDE . '/dbconnect.inc');

$sql = query('SELECT * FROM news WHERE irc_sent = \'FALSE\'');
while ($result = next_record($sql))
{
	$message = stripslashes($result['irc_message']);
	$return = send_irc_message($message);
}
query('UPDATE news SET irc_sent = \'TRUE\'');
query('OPTIMIZE TABLE `active_session`');

/*
// config file
include( realpath(dirname(__FILE__)) . '/../htdocs/config.inc');

// overwrite database class to use our db
include( LIB . 'global/smr_db.inc' );

// new db object
$player = new SMR_DB();
$cache = new SMR_DB();
$news = new SMR_DB();

$player->query('SELECT account_id, game_id, experience FROM player');
while ($player->next_record()) {

	$cache->query('REPLACE INTO player_cache
				   (account_id, game_id, experience)
				   VALUES(' . $player->f('account_id') . ', ' . $player->f('game_id') . ', ' . $player->f('experience') . ')
				  ');

}

// We take this opportunity to clear up some things
$cache->query('DELETE FROM active_session WHERE last_accessed<' . (time() - 1800));

$cache->query('DELETE FROM combat_logs WHERE timestamp<' . (time() - 172800) . ' AND saved = 0');

$cache->query('DELETE FROM locks_queue WHERE timestamp<' . (time() - 30));

$news->query('SELECT * FROM news WHERE sent_irc = \'FALSE\'');
$sent = array();
while ($news->next_record()) {
	$message = $news->f('news_message');
	$message = strip_tags($message);
	$message = str_replace('&nbsp;',' ',$message);
	$message = str_replace('&nbsp',' ',$message);
	$message = str_replace('	','',$message);
	$result = send_irc_message($message);
	$sent[] = $news->f('news_id');
}
$news->query('UPDATE news SET sent_irc = \'TRUE\'');
*/
function send_irc_message($message) {
	$fsockopen = @fsockopen('Chat.VJTD3.com', 80, $errorint, $errorstr, 15);
	@fwrite($fsockopen, 'GET /smrnews.php?'.rawurlencode($message).' HTTP/1.0'.EOL);
	unset($message);
	@fwrite($fsockopen, 'Host: Chat.VJTD3.com'.EOL);
	@fwrite($fsockopen, ''.EOL);
	if ($fsockopen)
	{
		while (!feof($fsockopen))
		{
			@$data .=fread($fsockopen, 1024);
		}
	}
	fclose($fsockopen);
	unset($fsockopen);
	$data = explode('\n'.EOL, $data, 2);
	$data = @$data['1'];
	return (@$data['0'] === '1' ? 1 : 0);
}


?>
