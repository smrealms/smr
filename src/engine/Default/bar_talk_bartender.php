<?php declare(strict_types=1);

$template = Smr\Template::getInstance();
$session = Smr\Session::getInstance();
$player = $session->getPlayer();

$template->assign('PageTopic', 'Talk to Bartender');
Menu::bar();

// We save the displayed message in session since it is randomized
if (!isset($var['Message'])) {
	$db = Smr\Database::getInstance();
	$db->query('SELECT * FROM bar_tender WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . ' ORDER BY rand() LIMIT 1');
	if ($db->nextRecord()) {
		$message = 'I heard... ' . htmlentities(word_filter($db->getField('message'))) . '<br /><br />Got anything else to tell me?';
	} else {
		$message = 'I havent heard anything recently... got anything to tell me?';
	}
	$session->updateVar('Message', $message);
}
$template->assign('Message', bbifyMessage($var['Message']));

$container = Page::create('skeleton.php', 'bar_talk_bartender.php');
$container->addVar('LocationID');
$template->assign('ListenHREF', $container->href());

$container = Page::create('bar_talk_bartender_processing.php');
$container->addVar('LocationID');
$template->assign('ProcessingHREF', $container->href());
