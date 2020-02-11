<?php declare(strict_types=1);

try {
	
	require_once('config.inc');
	require_once(LIB . 'Default/smr.inc');
	
	// ********************************
	// *
	// * S e s s i o n
	// *
	// ********************************
	
	
	if (SmrSession::hasAccount()) {
		// creates a new user account object
		$account = SmrSession::getAccount();
	
		// update last login column
		$account->updateLastLogin();

		$href = SmrSession::getNewHREF(create_container('login_check_processing.php'), true);
		SmrSession::update();
	
		header('Location: ' . $href);
		exit;
	}
	
	$template = new Template();
	if (Request::has('msg')) {
		$template->assign('Message', htmlentities(trim(Request::get('msg')), ENT_COMPAT, 'utf-8'));
	}

	// Get recent non-admin game news
	$gameNews = array();
	$db = new SmrMySqlDatabase();
	$db->query('SELECT * FROM news WHERE type != \'admin\' ORDER BY time DESC LIMIT 4');
	while ($db->nextRecord()) {
		$overrideGameID = $db->getInt('game_id'); //for bbifyMessage
		$gameNews[] = [
			'Time' => date(DEFAULT_DATE_FULL_SHORT_SPLIT, $db->getInt('time')),
			'Message' => bbifyMessage($db->getField('news_message')),
		];
	}
	$template->assign('GameNews', $gameNews);
	unset($overrideGameID);

	require_once(ENGINE . 'Default/login_story.inc');

	$template->assign('Body', 'login/login.php');
	$template->display('login/skeleton.php');

} catch (Throwable $e) {
	handleException($e);
}
