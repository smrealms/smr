<?php
$alliance =& SmrAlliance::getAlliance($var['alliance_id'], $player->getGameID());
$template->assign('PageTopic', $alliance->getAllianceName(false, true));
require_once(get_file_loc('menu.inc'));
create_alliance_menu($alliance->getAllianceID(),$alliance->getLeaderID());

$container = create_container('message_send_processing.php');
$container['alliance_id'] = $var['alliance_id'];
$template->assign('MessageSendFormHref',SmrSession::getNewHREF($container));

$template->assign('Receiver', 'Whole Alliance');
if(isset($var['preview'])) {
	$template->assign('Preview', $var['preview']);
}
?>
