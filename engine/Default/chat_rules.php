<?php

$template->assign('PageTopic','Space Merchant Realms Chat Room Rules');

$autoChannels = '#SMR';
if($player->hasAlliance()) {
	$autoChannels = $player->getAlliance()->getIrcChannel();
	if($allianceChan != '') {
		$allianceChan = ',' . urlencode($allianceChan);
	}
}
$template->assign('AutoChannels', $autoChannels);
?>