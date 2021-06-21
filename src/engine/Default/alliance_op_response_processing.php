<?php declare(strict_types=1);

$session = Smr\Session::getInstance();
$player = $session->getPlayer();

$response = strtoupper(Request::get('op_response'));

$db = Smr\Database::getInstance();
$db->write('REPLACE INTO alliance_has_op_response (alliance_id, game_id, account_id, response) VALUES (' . $db->escapeNumber($player->getAllianceID()) . ',' . $db->escapeNumber($player->getGameID()) . ',' . $db->escapeNumber($player->getAccountID()) . ', ' . $db->escapeString($response) . ')');

Page::create('skeleton.php', 'alliance_mod.php')->go();
