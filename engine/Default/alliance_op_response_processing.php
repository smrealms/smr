<?php declare(strict_types=1);

$response = strtoupper(Request::get('op_response'));

$db->query('REPLACE INTO alliance_has_op_response (alliance_id, game_id, player_id, response) VALUES (' . $db->escapeNumber($player->getAllianceID()) . ',' . $db->escapeNumber($player->getGameID()) . ',' . $db->escapeNumber($player->getPlayerID()) . ', ' . $db->escapeString($response) . ')');

forward(create_container('skeleton.php', 'alliance_mod.php'));
