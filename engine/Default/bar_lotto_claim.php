<?php declare(strict_types=1);

$message = '';
//check if we really are a winner
$db->query('SELECT * FROM player_has_ticket WHERE ' . $player->getSQL() . ' AND time = 0');
if ($db->nextRecord()) {
	$prize = $db->getInt('prize');
	$NHLAmount = IRound(($prize - 1000000) / 9);
	$db->query('UPDATE player SET bank = bank + ' . $db->escapeNumber($NHLAmount) . ' WHERE player_id = ' . $db->escapeNumber(PLAYER_ID_NHL) . ' AND game_id = ' . $db->escapeNumber($player->getGameID()));
	$player->increaseCredits($prize);
	$player->increaseHOF($prize, array('Bar', 'Lotto', 'Money', 'Claimed'), HOF_PUBLIC);
	$player->increaseHOF(1, array('Bar', 'Lotto', 'Results', 'Claims'), HOF_PUBLIC);
	$message .= '<div class="center">You have claimed <span class="red">$' . number_format($prize) . '</span>!<br /></div><br />';
	$db->query('DELETE FROM player_has_ticket WHERE ' . $player->getSQL() . ' AND prize = ' . $db->escapeNumber($prize) . ' AND time = 0 LIMIT 1');
	$db->query('DELETE FROM news WHERE type = \'lotto\' AND game_id = ' . $db->escapeNumber($player->getGameID()));
}
//offer another drink and such
$container = create_container('skeleton.php', 'bar_main.php');
transfer('LocationID');
$container['message'] = $message;
forward($container);
