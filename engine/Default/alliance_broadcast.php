<?php declare(strict_types=1);
$alliance = SmrAlliance::getAlliance($var['alliance_id'], $player->getGameID());
$template->assign('PageTopic', $alliance->getAllianceDisplayName(false, true));
Menu::alliance($alliance->getAllianceID(), $alliance->getLeaderID());

$container = create_container('message_send_processing.php');
$container['alliance_id'] = $var['alliance_id'];
$template->assign('MessageSendFormHref', SmrSession::getNewHREF($container));

$template->assign('Receiver', 'Whole Alliance');
if (isset($var['preview'])) {
	$template->assign('Preview', $var['preview']);
}
