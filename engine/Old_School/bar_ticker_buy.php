<?

$num_creds = $account->get_credits();

if (isset($var['process'])) {
	
	if ($num_creds == 0) {
		$PHP_OUTPUT.=create_echo_error('You don\'t have enough SMR Credits.  Donate money to SMR to gain SMR Credits!');
		return;	
	}
	$type = $_REQUEST['type'];
	$expires = time() + (5*24*60*60);
	//only scout OR news....but you can have both scout and block or news and block
	if ($type == 'scout' || $type == 'news') {
		$db->query('SELECT * FROM player_has_ticker WHERE game_id = '.$player->getGameID().' AND account_id = '.$player->getAccountID().' AND (type = \'news\' OR type = \'scout\')');
		if ($db->next_record()) $db->query('DELETE FROM player_has_ticker WHERE game_id = '.$player->getGameID().' AND account_id = '.$player->getAccountID().' AND (type = \'news\' OR type = \'scout\')');
		$db->query('INSERT INTO player_has_ticker (game_id, account_id, type, expires) VALUES ('.$player->getGameID().', '.$player->getAccountID().', '.$db->escapeString($type).', '.$expires.')');
	} else $db->query('REPLACE INTO player_has_ticker (game_id, account_id, type, expires) VALUES ('.$player->getGameID().', '.$player->getAccountID().', '.$db->escapeString($type).', '.$expires.')');
	//take money
	$account->set_credits($num_creds - 1);
	//offer another drink and such
	$PHP_OUTPUT.=('<div align=center>Your system has been added.  Enjoy!</div><br>');
	include(get_file_loc('bar_opening.php'));

} else {
	
	//they can buy the ticker...first we need to find out what they want
	$db->query('SELECT * FROM player_has_ticker WHERE game_id = '.$player->getGameID().' AND account_id = '.$player->getAccountID());
	while ($db->next_record()) {
		$expire = $db->f('expires');
		$type = $db->f('type');
		if ($type == 'news') $type = 'News Ticker';
		if ($type == 'scout') $type = 'Scout Message Ticker';
		if ($type == 'block') $type = 'Scout Message Blocker';
		$left = $expire - time();
		$days = floor($left / 86400);
		$left -= $days * 86400;
		$hours = floor($left / 3600);
		$left -= $hours * 3600;
		$mins = floor($left / 60);
		$left -= $mins * 60;
		$remain = $days.' Days, '.$hours.' Hours, '.$mins.' Minutes, '.$left.' Seconds';
		$PHP_OUTPUT.=('You own a '.$type.' for another '.$remain.'.<br>');
		if ($type == 'News Ticker') $PHP_OUTPUT.=('Note: If you select Scout Message Ticker you will lose your Current News Ticker<br>');
		if ($type == 'Scout Message Ticker') $PHP_OUTPUT.=('Note: If you select Current News Ticker you will lose your Scout Message Ticker<br>');
	}
	$PHP_OUTPUT.=('Great idea!  So what do you want us to configure your system to do?<br>');
	$container = array();
	$container['url'] = 'skeleton.php';
	$container['body'] = 'bar_main.php';
	$container['script'] = 'bar_ticker_buy.php';
	$container['process'] = 'yes';
	$PHP_OUTPUT.=create_echo_form($container);
	$PHP_OUTPUT.=('<input type=radio name=type value=scout>Send Scout Messages<br>');
	$PHP_OUTPUT.=('<input type=radio name=type value=news>Send Recent News<br>');
	$PHP_OUTPUT.=('<input type=radio name=type value=block>Block Scout Message Tickers<br /><small>This will only block messages to tickers, it will not completely block scout messages</small><br>');
	$PHP_OUTPUT.=create_submit('Continue');
	$PHP_OUTPUT.=('</form>');
}

?>