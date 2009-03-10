<?
require_once(get_file_loc('smr_alliance.inc'));

$alliance = new SMR_ALLIANCE($player->getAllianceID(), SmrSession::$game_id);
$action = $var['action'];
include(get_file_loc('alliance_members.php'));
if ($action == 'YES') {

	$db->query('SELECT * FROM player WHERE alliance_id = '.$player->getAllianceID().' AND ' .
										  'game_id = '.$player->getGameID());

	// will this alliance be empty if we leave? (means one member right now)
	if ($db->getNumRows() == 1) {

		//$db->query('DELETE FROM alliance WHERE alliance_id = '.$player->getAllianceID().' AND ' .
											  //'game_id = '.$player->getGameID());
		$db->query('DELETE FROM alliance_bank_transactions ' .
				   'WHERE alliance_id = '.$player->getAllianceID().' AND ' .
						 'game_id = '.$player->getGameID());
		$db->query('DELETE FROM alliance_thread ' .
				   'WHERE alliance_id = '.$player->getAllianceID().' AND ' .
						 'game_id = '.$player->getGameID());
		$db->query('DELETE FROM alliance_thread_topic ' .
				   'WHERE alliance_id = '.$player->getAllianceID().' AND ' .
						 'game_id = '.$player->getGameID());
		$db->query('DELETE FROM alliance_has_roles
					WHERE alliance_id = '.$player->getAllianceID().' AND
						  game_id = '.$player->getGameID());
		$db->query('UPDATE alliance SET leader_id=0 WHERE alliance_id = '.$player->getAllianceID().' AND ' .
						 'game_id = '.$player->getGameID());

	} elseif ($alliance->leader_id == $player->getAccountID())
		create_error('You are the leader! You must hand over leadership first!');

	if ($alliance->leader_id != $player->getAccountID())
		$player->sendMessage($alliance->leader_id, 2, $db->escape_string('I left your alliance!', false));

	$player->setAllianceID(0);
	$player->update();

	$db->query('DELETE
				FROM player_has_alliance_role
				WHERE account_id = '.$player->getAccountID().' AND
					  game_id = '.$player->getGameID());

	$account->log(3, 'left alliance: '.$alliance->alliance_name, $player->getSectorID());

}

$container = array();
$container['url'] = 'skeleton.php';
if ($player->isLandedOnPlanet())
	$container['body'] = 'planet_main.php';
else
	$container['body'] = 'current_sector.php';

forward($container);

?>