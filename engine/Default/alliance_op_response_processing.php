<?php

if (!isset($_POST['op_response'])) {
	create_error('No op response specified!');
}

$response = strtoupper($_POST['op_response']);

$db->query('REPLACE INTO alliance_has_op_response (alliance_id, game_id, account_id, response) VALUES (' . $db->escapeNumber($player->getAllianceID()) . ',' . $db->escapeNumber($player->getGameID()) . ',' . $db->escapeNumber($player->getAccountID()) . ', ' . $db->escapeString($response) . ')');

forward(create_container('skeleton.php', 'alliance_mod.php'));
