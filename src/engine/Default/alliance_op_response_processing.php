<?php declare(strict_types=1);

$response = strtoupper(Request::get('op_response'));

$db->query('REPLACE INTO alliance_has_op_response (alliance_id, game_id, account_id, response) VALUES (' . $db->escapeNumber($player->getAllianceID()) . ',' . $db->escapeNumber($player->getGameID()) . ',' . $db->escapeNumber($player->getAccountID()) . ', ' . $db->escapeString($response) . ')');

Page::create('skeleton.php', 'alliance_mod.php')->go();
