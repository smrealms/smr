<?php declare(strict_types=1);

use Smr\Database;
use Smr\Lotto;

$template = Smr\Template::getInstance();
$session = Smr\Session::getInstance();
$player = $session->getPlayer();

$template->assign('PageTopic', 'Savings');

Menu::trader();

$anonAccounts = [];
$db = Database::getInstance();
$dbResult = $db->read('SELECT * FROM anon_bank WHERE owner_id = ' . $db->escapeNumber($player->getAccountID()) . ' AND game_id = ' . $db->escapeNumber($player->getGameID()));
foreach ($dbResult->records() as $dbRecord) {
	$anonAccounts[] = [
		'ID' => $dbRecord->getInt('anon_id'),
		'Password' => $dbRecord->getString('password'),
	];
}
$template->assign('AnonAccounts', $anonAccounts);

Lotto::checkForLottoWinner($player->getGameID());
$template->assign('LottoInfo', Lotto::getLottoInfo($player->getGameID()));

// Number of active lotto tickets this player has
$dbResult = $db->read('SELECT count(*) FROM player_has_ticket WHERE ' . $player->getSQL() . ' AND time > 0');
$tickets = $dbResult->record()->getInt('count(*)');
$template->assign('LottoTickets', $tickets);

// Number of active lotto tickets all players have
$dbResult = $db->read('SELECT count(*) FROM player_has_ticket WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . ' AND time > 0');
$tickets_tot = $dbResult->record()->getInt('count(*)');
if ($tickets == 0) {
	$win_chance = 0;
} else {
	$win_chance = round(100 * $tickets / $tickets_tot, 2);
}
$template->assign('LottoWinChance', $win_chance);

// Number of winning lotto tickets this player has to claim
$dbResult = $db->read('SELECT count(*) FROM player_has_ticket WHERE ' . $player->getSQL() . ' AND time = 0');
$template->assign('WinningTickets', $dbResult->record()->getInt('count(*)'));
