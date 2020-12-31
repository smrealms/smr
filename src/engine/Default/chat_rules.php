<?php declare(strict_types=1);

$template->assign('PageTopic', 'Space Merchant Realms Chat');


$autoChannels = '#SMR';
$nick = 'SMR-';
if (isset($player) && $player->hasAlliance()) {
	$allianceChan = $player->getAlliance()->getIrcChannel();
	if ($allianceChan) {
		$autoChannels .= ',' . $allianceChan;
	}
	$nick .= $player->getPlayerName();
} else {
	$nick .= $account->getHofName();
}

$ircURL = 'http://widget.mibbit.com/?settings=5f6a385735f22a3138c5cc6059dab2f4&server=irc.theairlock.net&autoconnect=true&channel=' . urlencode($autoChannels) . '&nick=' . urlencode(str_replace(' ', '_', $nick));
$template->assign('IrcURL', $ircURL);
