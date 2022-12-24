<?php declare(strict_types=1);

use Smr\Database;
use Smr\Pages\Account\LoginCheckValidatedProcessor;
use Smr\Request;

try {

	require_once('../bootstrap.php');

	// ********************************
	// *
	// * S e s s i o n
	// *
	// ********************************

	$session = Smr\Session::getInstance();
	if ($session->hasAccount()) {
		$href = (new LoginCheckValidatedProcessor())->href(true);
		$session->update();

		header('Location: ' . $href);
		exit;
	}

	$template = Smr\Template::getInstance();
	if (Request::has('msg')) {
		$template->assign('Message', htmlentities(Request::get('msg'), ENT_COMPAT, 'utf-8'));
	} elseif (Request::has('status')) {
		session_start();
		if (isset($_SESSION['login_msg'])) {
			$template->assign('Message', $_SESSION['login_msg']);
		}
		session_destroy();
	}

	// Get recent non-admin game news
	$gameNews = [];
	$db = Database::getInstance();
	$dbResult = $db->read('SELECT * FROM news WHERE type != \'admin\' ORDER BY time DESC LIMIT 4');
	foreach ($dbResult->records() as $dbRecord) {
		$gameNews[] = [
			'Time' => date(DEFAULT_DATE_TIME_FORMAT_SPLIT, $dbRecord->getInt('time')),
			'Message' => bbifyMessage(
				$dbRecord->getString('news_message'),
				$dbRecord->getInt('game_id'),
			),
		];
	}
	$template->assign('GameNews', $gameNews);

	// SMR game blurb
	$story = [
		'It is a time of great interstellar turmoil....',
		'The decline of the Galactic Federation, which served for centuries as a glowing bastion of peace, freedom, and prosperity, has given way to a rising tide of corruption and organized crime. Trade lanes are unsafe even in the most well populated areas. Pirates disrupt the free flow of cash and goods to local economies. Organized gangs have laid claim to entire planets and control the most lucrative ports by threat of violence. Black market goods are sold in the open without fear.',
		'Sensing that the Federation has lost its grip on power, the eight known races have split off and formed independent governments. The presidents and their councils guide the flow of weapons and trade in a bid to retain economic control and keep their people safe. Despite these efforts, violence and desperation continue to escalate.',
		'This is the universe you find yourself in.',
		'Space Merchant Realms is a game of speed, skill, and strategy. To make it to the top ranks, it takes a combination of leadership, persistence, courage, and cooperation. Will you quest for riches as a tradeship captain? Or seek fame and glory as an alliance fleet commander? Is the life of a pirate what you are after? Where will your destiny take you?',
	];
	$template->assign('Story', $story);

	$template->assign('Body', 'login/login.php');
	$template->display('login/skeleton.php');

} catch (Throwable $e) {
	handleException($e);
}
