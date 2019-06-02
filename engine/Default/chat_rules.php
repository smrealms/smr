<?php

$template->assign('PageTopic', 'Space Merchant Realms Chat');

$autoChannels = urlencode('#SMR');
if ($player->hasAlliance()) {
	$allianceChan = $player->getAlliance()->getIrcChannel();
	if ($allianceChan != '') {
		$autoChannels .= ',' . urlencode($allianceChan);
	}
}
$template->assign('AutoChannels', $autoChannels);
