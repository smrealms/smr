<?php

$template->assign('PageTopic','Space Merchant Realms Chat Room Rules');

$autoChannels = '#SMR';
if($player->hasAlliance()) {
	$allianceChan = $player->getAlliance()->getIrcChannel();
	if($allianceChan != '') {
		$autoChannels .= ',' . urlencode($allianceChan);
	}
}
$template->assign('AutoChannels', $autoChannels);
?>