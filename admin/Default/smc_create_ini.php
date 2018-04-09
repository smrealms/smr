<?php

header('Content-Type: text/plain; charset=ISO-8859-1');
header('Content-Disposition: attachment; filename="Game.ini"');
header('Content-transfer-encoding: base64');

$game_id = $_REQUEST['game_id'];

echo ('[Settings]'.EOL);
echo ('Name=' . Globals::getGameName($game_id) . EOL);
echo ('ID=' . $game_id . EOL.EOL);

echo ('[Galaxy]'.EOL);
$gameGals =& SmrGalaxy::getGameGalaxies($game_id);
foreach($gameGals as &$gameGal) {
	echo ($gameGal->getName().'='.$gameGal->getWidth().';'.$gameGal->getHeight().EOL);
} unset($gameGal);

// expire all forces first
$db->query('DELETE FROM sector_has_forces WHERE expire_time < ' . TIME);

echo (EOL.'[Marks]'.EOL);
$db->query('SELECT * FROM sector_has_forces WHERE game_id = '.$game_id.' GROUP BY sector_id ORDER BY sector_id');
while ($db->nextRecord()) {
	$owner =& SmrPlayer::getPlayer($db->getField('owner_id'), $game_id);
	$user =& SmrPlayer::getPlayer($account->getAccountID(), $game_id);
	$sector = $db->getField('sector_id');

	if ($owner->sameAlliance($user))
		echo ($sector.'=2'.EOL);
	else
		echo ($sector.'=1'.EOL);
}
