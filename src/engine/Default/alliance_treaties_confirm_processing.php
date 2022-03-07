<?php declare(strict_types=1);

$session = Smr\Session::getInstance();
$var = $session->getCurrentVar();
$player = $session->getPlayer();

$alliance1 = $player->getAlliance();
$alliance2 = SmrAlliance::getAlliance($var['proposedAlliance'], $player->getGameID());

$alliance_id_1 = $alliance1->getAllianceID();
$alliance_id_2 = $alliance2->getAllianceID();

$db = Smr\Database::getInstance();
$db->insert('alliance_treaties', [
	'alliance_id_1' => $db->escapeNumber($alliance_id_1),
	'alliance_id_2' => $db->escapeNumber($alliance_id_2),
	'game_id' => $db->escapeNumber($player->getGameID()),
	'trader_assist' => $db->escapeBoolean($var['trader_assist']),
	'trader_defend' => $db->escapeBoolean($var['trader_defend']),
	'trader_nap' => $db->escapeBoolean($var['trader_nap']),
	'raid_assist' => $db->escapeBoolean($var['raid_assist']),
	'planet_land' => $db->escapeBoolean($var['planet_land']),
	'planet_nap' => $db->escapeBoolean($var['planet_nap']),
	'forces_nap' => $db->escapeBoolean($var['forces_nap']),
	'aa_access' => $db->escapeBoolean($var['aa_access']),
	'mb_read' => $db->escapeBoolean($var['mb_read']),
	'mb_write' => $db->escapeBoolean($var['mb_write']),
	'mod_read' => $db->escapeBoolean($var['mod_read']),
	'official' => $db->escapeBoolean(false),
]);

//send a message to the leader letting them know the offer is waiting.
$leader2 = $alliance2->getLeaderID();
$message = 'An ambassador from ' . $alliance1->getAllianceBBLink() . ' has arrived with a treaty offer.';

SmrPlayer::sendMessageFromAllianceAmbassador($player->getGameID(), $leader2, $message);
$container = Page::create('skeleton.php', 'alliance_treaties.php');
$container['message'] = 'The treaty offer has been sent.';
$container->go();
