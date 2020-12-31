<?php declare(strict_types=1);
if (!isset($var['alliance_id'])) {
	SmrSession::updateVar('alliance_id', $player->getAllianceID());
}
$alliance_id = $var['alliance_id'];
if (Request::has('description')) {
	$description = trim(Request::get('description'));
}
if (Request::has('discord_server')) {
	$discordServer = trim(Request::get('discord_server'));
}
if (Request::has('discord_channel')) {
	$discordChannel = trim(Request::get('discord_channel'));
}
if (Request::has('irc')) {
	$irc = trim(Request::get('irc'));
}
if (Request::has('mod')) {
	$mod = trim(Request::get('mod'));
}
if (Request::has('url')) {
	$url = trim(Request::get('url'));
}

// Prevent XSS attacks
if (isset($url) && preg_match('/"/', $url)) {
	create_error('You cannot use a " in the image link!');
}

$alliance = SmrAlliance::getAlliance($alliance_id, $player->getGameID());
if (Request::has('recruit_type')) {
	$recruitType = Request::get('recruit_type');
	$password = Request::get('password', '');
	$alliance->setRecruitType($recruitType, $password);
}
if (isset($description)) {
	$alliance->setAllianceDescription($description);
}
if (isset($discordServer)) {
	$alliance->setDiscordServer($discordServer);
}
if (isset($discordChannel)) {
	if (empty($discordChannel)) {
		$alliance->setDiscordChannel(null);
	} else {
		// no duplicates in a given game
		$db->query('SELECT * FROM alliance WHERE discord_channel =' . $db->escapeString($discordChannel) . ' AND game_id = ' . $db->escapeNumber($alliance->getGameID()) . ' AND alliance_id != ' . $db->escapeNumber($alliance->getAllianceID()) . ' LIMIT 1');
		if ($db->nextRecord()) {
			create_error('Another alliance is already using that Discord Channel ID!');
		}

		$alliance->setDiscordChannel($discordChannel);
	}
}
if (isset($irc)) {
	$alliance->setIrcChannel($irc);
}
if (isset($mod)) {
	$alliance->setMotD($mod);
}
if (isset($url)) {
	$alliance->setImageURL($url);
}

$alliance->update();
$container = create_container('skeleton.php', 'alliance_roster.php');
$container['alliance_id'] = $alliance_id;
forward($container);
