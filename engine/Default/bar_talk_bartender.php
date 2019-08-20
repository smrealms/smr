<?php declare(strict_types=1);

$template->assign('PageTopic', 'Talk to Bartender');
Menu::bar();

// We don't save this in session because we only want to insert once
if (isset($_REQUEST['gossip_tell'])) {
	$db->query('SELECT message_id FROM bar_tender WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . ' ORDER BY message_id DESC LIMIT 1');
	if ($db->nextRecord()) {
		$amount = $db->getInt('message_id') + 1;
	} else {
		$amount = 1;
	}

	$db->query('INSERT INTO bar_tender (game_id, message_id, message) VALUES (' . $db->escapeNumber($player->getGameID()) . ', ' . $db->escapeNumber($amount) . ',  ' . $db->escapeString($_REQUEST['gossip_tell']) . ' )');
	SmrAccount::doMessageSendingToBox($player->getAccountID(), BOX_BARTENDER, $_REQUEST['gossip_tell'], $player->getGameID());

	SmrSession::updateVar('Message', 'Huh, that\'s news to me...<br /><br />Got anything else to tell me?');
}

// We save the displayed message in session since it is randomized
if (!isset($var['Message'])) {
	$db->query('SELECT * FROM bar_tender WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . ' ORDER BY rand() LIMIT 1');
	if ($db->nextRecord()) {
		$message = 'I heard... ' . $db->getField('message') . '<br /><br />Got anything else to tell me?';
	} else {
		$message = 'I havent heard anything recently... got anything to tell me?';
	}
	SmrSession::updateVar('Message', $message);
} else {
	$message = $var['Message'];
}
$template->assign('Message', $message);

$container = create_container('skeleton.php', 'bar_talk_bartender.php');
transfer('LocationID');
$template->assign('GossipHREF', SmrSession::getNewHREF($container));
