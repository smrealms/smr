<?php

if (isset($var['process'])) {
	if ($account->getTotalSmrCredits() == 0) {
		create_error('You don\'t have enough SMR Credits.  Donate money to SMR to gain SMR Credits!');
	}
	if(isset($_REQUEST['type'])) {
		SmrSession::updateVar('type',$_REQUEST['type']);
	}
	$type = $var['type'];
	if(empty($type)) {
		create_error('You have to choose the type of ticker to buy.');
	}
	switch($type) {
		case 'NEWS':
		case 'SCOUT':
		case 'BLOCK':
		break;
		default:
			create_error('The ticker you chose does not exist.');
	}
	$expires = TIME;
	$ticker = $player->getTicker($type);
	if($ticker !== false) {
		$expires = $ticker['Expires'];
	}
	$expires += 5*86400;
	$db->query('REPLACE INTO player_has_ticker (game_id, account_id, type, expires) VALUES (' . $db->escapeNumber($player->getGameID()) . ', ' . $db->escapeNumber($player->getAccountID()) . ', ' . $db->escapeString($type) . ', ' . $db->escapeNumber($expires) . ')');
	//take credits
	$account->decreaseTotalSmrCredits(1);
	//offer another drink and such
	$container=create_container('skeleton.php','bar_main.php');
	$container['script']='bar_opening.php';
	$container['message'] = '<div align="center">Your system has been added.  Enjoy!</div><br />';
	forward($container);
}
else {
	//they can buy the ticker...first we need to find out what they want
	$tickers = $player->getTickers();
	foreach($tickers as $ticker) {
		$type = $ticker['Type'];
		if ($ticker['Type'] == 'NEWS') {
			$type = 'News Ticker';
		}
		if ($ticker['Type'] == 'SCOUT') {
			$type = 'Scout Message Ticker';
		}
		if ($ticker['Type'] == 'BLOCK') {
			$type = 'Scout Message Blocker';
		}
		$left = $ticker['Expires'] - TIME;
		$days = floor($left / 86400);
		$left -= $days * 86400;
		$hours = floor($left / 3600);
		$left -= $hours * 3600;
		$mins = floor($left / 60);
		$left -= $mins * 60;
		$remain = $days.' Days, '.$hours.' Hours, '.$mins.' Minutes, '.$left.' Seconds';
		$PHP_OUTPUT.=('You own a '.$type.' for another '.$remain.'.<br />');
//		if ($type == 'News Ticker') $PHP_OUTPUT.=('Note: If you select Scout Message Ticker you will lose your Current News Ticker<br />');
//		if ($type == 'Scout Message Ticker') $PHP_OUTPUT.=('Note: If you select Current News Ticker you will lose your Scout Message Ticker<br />');
	}
	$PHP_OUTPUT.=('Great idea!  So what do you want us to configure your system to do?<br />');
	$container = create_container('skeleton.php', 'bar_main.php');
	$container['script'] = 'bar_ticker_buy.php';
	$container['process'] = 'yes';
	$PHP_OUTPUT.=create_echo_form($container);
	$PHP_OUTPUT.=('<input type="radio" name="type" value="SCOUT">Send Scout Messages<br />');
	$PHP_OUTPUT.=('<input type="radio" name="type" value="NEWS">Send Recent News<br />');
	$PHP_OUTPUT.=('<input type="radio" name="type" value="BLOCK">Block Scout Message Tickers<br /><small>This will only block messages to tickers, it will not completely block scout messages</small><br />');
	$PHP_OUTPUT.=create_submit('Continue');
	$PHP_OUTPUT.=('</form>');
}

?>