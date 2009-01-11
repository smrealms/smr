<?

if ($player->getGameID() == 0)
	return;

// create a date from last midnight
$midnight = mktime(0, 0, 0, date('m'), date('d'), date('Y'));

$db->query('SELECT * FROM player_votes_twg ' .
		   'WHERE account_id = '.$player->getAccountID().' AND ' .
				 'game_id = '.$player->getGameID().' AND ' .
				 'time > '.$midnight);
if ($db->getNumRows() > 0)
	return;

// give him 5 turns
$player->giveTurns(5);

// make it permanent
$player->update();
$db->query('SELECT * FROM game WHERE game_id = '.$player->getGameID());
$db->nextRecord();
$type = $db->getField('game_type');

$db->query('UPDATE account_has_stats ' .
		   'SET bonus_turns = bonus_turns + 5 ' .
		   'WHERE account_id = '.$player->getAccountID().' AND game_type = '.$db->escapeString($type));

$db->query('REPLACE INTO player_votes_twg ' .
		   '(account_id, game_id, time) ' .
		   'VALUES('.$player->getAccountID().', '.$player->getGameID().', ' . TIME . ')');

header('Location: http://www.topwebgames.com/in.asp?id=136');

?>