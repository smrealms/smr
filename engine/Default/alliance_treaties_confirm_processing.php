<?php

$alliance1 = $player->getAlliance();
$alliance2 = SmrAlliance::getAlliance($var['proposedAlliance'], $player->getGameID());

$alliance_id_1 = $alliance1->getAllianceID();
$alliance_id_2 = $alliance2->getAllianceID();

$db->query('INSERT INTO alliance_treaties (alliance_id_1,alliance_id_2,game_id,trader_assist,trader_defend,trader_nap,raid_assist,planet_land,planet_nap,forces_nap,aa_access,mb_read,mb_write,mod_read,official)
			VALUES (' . $db->escapeNumber($alliance_id_1) . ', ' . $db->escapeNumber($alliance_id_2) . ', ' . $db->escapeNumber($player->getGameID()) . ', ' . $db->escapeBoolean($var['trader_assist']) . ', ' .
			$db->escapeBoolean($var['trader_defend']) . ', ' . $db->escapeBoolean($var['trader_nap']) . ', ' . $db->escapeBoolean($var['raid_assist']) . ', ' . $db->escapeBoolean($var['planet_land']) . ', ' . $db->escapeBoolean($var['planet_nap']) . ', ' .
			$db->escapeBoolean($var['forces_nap']) . ', ' . $db->escapeBoolean($var['aa_access']) . ', ' . $db->escapeBoolean($var['mb_read']) . ', ' . $db->escapeBoolean($var['mb_write']) . ', ' . $db->escapeBoolean($var['mod_read']) . ', \'FALSE\')');

//send a message to the leader letting them know the offer is waiting.
$leader2 = $alliance2->getLeaderID();
$message = 'An ambassador from [alliance=' . $alliance1->getAllianceID() . '] has arrived with a treaty offer.';

SmrPlayer::sendMessageFromAllianceAmbassador($player->getGameID(), $leader2, $message);
$container = create_container('skeleton.php', 'alliance_treaties.php');
$container['message'] = 'The treaty offer has been sent.';
forward($container);
