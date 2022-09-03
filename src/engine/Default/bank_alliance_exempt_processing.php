<?php declare(strict_types=1);

use Smr\Database;
use Smr\Request;

$db = Database::getInstance();
$session = Smr\Session::getInstance();
$var = $session->getCurrentVar();
$player = $session->getPlayer();

//only if we are coming from the bank screen do we unexempt selection first
if (isset($var['minVal'])) {
	$db->write('UPDATE alliance_bank_transactions SET exempt = 0 WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . ' AND alliance_id = ' . $db->escapeNumber($player->getAllianceID()) . '
				AND transaction_id BETWEEN ' . $db->escapeNumber($var['minVal']) . ' AND ' . $db->escapeNumber($var['maxVal']));
}

if (Request::has('exempt')) {
	$trans_ids = array_keys(Request::getArray('exempt'));
	$db->write('UPDATE alliance_bank_transactions SET exempt = 1, request_exempt = 0 WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . ' AND alliance_id = ' . $db->escapeNumber($player->getAllianceID()) . '
				AND transaction_id IN (' . $db->escapeArray($trans_ids) . ')');
}

if (isset($var['minVal'])) {
	$container = Page::create('bank_alliance.php');
} else {
	$container = Page::create('alliance_exempt_authorize.php');
}
$container->go();
