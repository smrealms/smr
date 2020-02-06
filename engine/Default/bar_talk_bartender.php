<?php declare(strict_types=1);

$template->assign('PageTopic', 'Talk to Bartender');
Menu::bar();

// We save the displayed message in session since it is randomized
if (!isset($var['Message'])) {
	$db->query('SELECT * FROM bar_tender WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . ' ORDER BY rand() LIMIT 1');
	if ($db->nextRecord()) {
		$message = 'I heard... ' . $db->getField('message') . '<br /><br />Got anything else to tell me?';
	} else {
		$message = 'I havent heard anything recently... got anything to tell me?';
	}
	SmrSession::updateVar('Message', $message);
}
$template->assign('Message', $var['Message']);

$container = create_container('skeleton.php', 'bar_talk_bartender.php');
transfer('LocationID');
$template->assign('ListenHREF', SmrSession::getNewHREF($container));

$container = create_container('bar_talk_bartender_processing.php');
transfer('LocationID');
$template->assign('GossipHREF', SmrSession::getNewHREF($container));
