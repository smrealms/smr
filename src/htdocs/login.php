<?php declare(strict_types=1);

try {

	require_once('../bootstrap.php');

	// ********************************
	// *
	// * S e s s i o n
	// *
	// ********************************

	$session = Smr\Session::getInstance();
	if ($session->hasAccount()) {
		// update last login column
		$session->getAccount()->updateLastLogin();

		$href = Page::create('login_check_processing.php')->href(true);
		$session->update();

		header('Location: ' . $href);
		exit;
	}

	$template = Smr\Template::getInstance();
	if (Smr\Request::has('msg')) {
		$template->assign('Message', htmlentities(trim(Smr\Request::get('msg')), ENT_COMPAT, 'utf-8'));
	} elseif (Smr\Request::has('status')) {
		session_start();
		if (isset($_SESSION['login_msg'])) {
			$template->assign('Message', $_SESSION['login_msg']);
		}
		session_destroy();
	}

	// Get recent non-admin game news
	$gameNews = [];
	$db = Smr\Database::getInstance();
	$dbResult = $db->read('SELECT * FROM news WHERE type != \'admin\' ORDER BY time DESC LIMIT 4');
	foreach ($dbResult->records() as $dbRecord) {
		$overrideGameID = $dbRecord->getInt('game_id'); //for bbifyMessage
		$gameNews[] = [
			'Time' => date(DEFAULT_DATE_TIME_FORMAT_SPLIT, $dbRecord->getInt('time')),
			'Message' => bbifyMessage($dbRecord->getString('news_message')),
		];
	}
	$template->assign('GameNews', $gameNews);
	unset($overrideGameID);

	require_once(ENGINE . 'Default/login_story.inc.php');

	$template->assign('Body', 'login/login.php');
	$template->display('login/skeleton.php');

} catch (Throwable $e) {
	handleException($e);
}
