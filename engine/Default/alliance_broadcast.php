<?php
$alliance =& SmrAlliance::getAlliance($var['alliance_id'], $player->getGameID());
$template->assign('PageTopic',$alliance->getAllianceName() . ' (' . $alliance->getAllianceID() . ')');
require_once(get_file_loc('menue.inc'));
create_alliance_menue($alliance->getAllianceID(),$alliance->getLeaderID());

$container = create_container('message_send_processing.php');
$container['alliance_id'] = $var['alliance_id'];
$template->assign('MessageSendFormHref',SmrSession::get_new_href($container));

$template->assign('Reciever', 'Whole Alliance');
if(isset($var['preview']))
	$template->assign('Preview', $var['preview']);
?>