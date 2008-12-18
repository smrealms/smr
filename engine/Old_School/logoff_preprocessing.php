<?
SmrSession::$game_id = 0;
SmrSession::update();

// try to get a real ip first
if (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))
	$curr_ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
else
	$curr_ip = $_SERVER['REMOTE_ADDR'];

// log?
$account->log(1, 'logged off from '.$curr_ip);
$container = array();
$container['body'] ='logoff.php';
$container['url'] = 'skeleton.php';
$container['logoff'] = 'yes';
forward($container);

?>