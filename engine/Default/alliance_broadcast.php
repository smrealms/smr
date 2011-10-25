<?php

$db->query('SELECT leader_id FROM alliance WHERE game_id=' . $player->getGameID() . ' AND alliance_id=' . $player->getAllianceID() . ' LIMIT 1');
$db->nextRecord();
$template->assign('PageTopic',$player->getAllianceName() . ' (' . $player->getAllianceID() . ')');
require_once(get_file_loc('menue.inc'));
create_alliance_menue($player->getAllianceID(),$db->getField('leader_id'));

$container = create_container('message_send_processing.php');
$container['alliance_id'] = $var['alliance_id'];
$template->assign('MessageSendFormHref',SmrSession::get_new_href($container));

$template->assign('Reciever', 'Whole Alliance');
if(isset($var['preview']))
	$template->assign('Preview', $var['preview']);
?>