<?

// delete their entry in kills table
//$db->query('DELETE FROM kills WHERE dead_id = $player->getAccountID() AND game_id = '.$player->getGameID());

$smarty->assign('PageTopic','DEATH');

$PHP_OUTPUT.=('<p>As the hull of your ship collapses, you quickly launch out in your escape pod. ');
$PHP_OUTPUT.=('Activating the emergency warp system, your stomach turns as you are hurled through hyperspace back to a safe destination.</p>');

$PHP_OUTPUT.=('<p><img src="images/escape_pod.jpg"></p>');

/*
if ($player->getNewbieTurns() < 100)
	$player->getNewbieTurns() = 100;

if ($player->getSectorID() != $player->get_home())
	$player->getSectorID() = $player->get_home();

if ($player->getCredits() < 5000)
	$player->getCredits() = 5000;
*/
//$player->dead = 'FALSE';

//$player->deletePlottedCourse();

//$player->update();

$db->query('UPDATE player SET dead=\'FALSE\' WHERE account_id=' . SmrSession::$account_id . ' AND game_id=' . SmrSession::$game_id . ' LIMIT 1');

$account->log(8, 'Player sees death screen', $player->getSectorID());

?>