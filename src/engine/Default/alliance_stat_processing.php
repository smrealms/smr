<?php declare(strict_types=1);

$session = Smr\Session::getInstance();
$var = $session->getCurrentVar();
$player = $session->getPlayer();

$alliance_id = $var['alliance_id'] ?? $player->getAllianceID();
if (Smr\Request::has('description')) {
	$description = Smr\Request::get('description');
}
if (Smr\Request::has('discord_server')) {
	$discordServer = Smr\Request::get('discord_server');
}
if (Smr\Request::has('discord_channel')) {
	$discordChannel = Smr\Request::get('discord_channel');
}
if (Smr\Request::has('irc')) {
	$irc = Smr\Request::get('irc');
}
if (Smr\Request::has('mod')) {
	$mod = Smr\Request::get('mod');
}
if (Smr\Request::has('url')) {
	$url = Smr\Request::get('url');
}

// Prevent XSS attacks
if (isset($url) && preg_match('/"/', $url)) {
	create_error('You cannot use a " in the image link!');
}

$alliance = SmrAlliance::getAlliance($alliance_id, $player->getGameID());
if (Smr\Request::has('recruit_type')) {
	$recruitType = Smr\Request::get('recruit_type');
	$password = Smr\Request::get('password', '');
	$alliance->setRecruitType($recruitType, $password);
}
if (isset($description)) {
	$alliance->setAllianceDescription($description, $player);
}
if (isset($discordServer)) {
	$alliance->setDiscordServer($discordServer);
}
if (isset($discordChannel)) {
	if (empty($discordChannel)) {
		$alliance->setDiscordChannel(null);
	} else {
		// no duplicates in a given game
		$db = Smr\Database::getInstance();
		$dbResult = $db->read('SELECT 1 FROM alliance WHERE discord_channel =' . $db->escapeString($discordChannel) . ' AND game_id = ' . $db->escapeNumber($alliance->getGameID()) . ' AND alliance_id != ' . $db->escapeNumber($alliance->getAllianceID()) . ' LIMIT 1');
		if ($dbResult->hasRecord()) {
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
$container = Page::create('alliance_roster.php');
$container['alliance_id'] = $alliance_id;
$container->go();
