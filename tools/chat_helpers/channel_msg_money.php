<?php

function shared_channel_msg_money($player) {
	// Insist the player is in an alliance, otherwise this reports data
	// for ALL allianceless (i.e. alliance=0) players.
	if (!$player->hasAlliance()) {
		return array('This command can only be used when you are in an alliance.');
	}

	$result = array();

	// get money from AA
	$db = new SmrMySqlDatabase();
	$db->query('SELECT alliance_account FROM alliance WHERE alliance_id = ' . $player->getAllianceID() . ' AND game_id = ' . $player->getGameID());

	if ($db->nextRecord()) {
		$result[] = 'The alliance has ' . number_format($db->getField('alliance_account')) . ' credits in the bank account.';
	}

	// get money on ships and personal bank accounts
	$db->query('SELECT sum(credits) as total_onship, sum(bank) as total_onbank FROM player WHERE alliance_id = ' . $player->getAllianceID() . ' AND game_id = ' . $player->getGameID());

	if ($db->nextRecord()) {
		$result[] = 'Alliance members carry a total of ' . number_format($db->getField('total_onship')) . ' credits with them';
		$result[] = 'and keep a total of ' . number_format($db->getField('total_onbank')) . ' credits in their personal bank accounts.';
	}

	// get money on planets
	$db->query('SELECT SUM(credits) AS total_credits, SUM(bonds) AS total_bonds FROM planet WHERE game_id = ' . $player->getGameID() . ' AND owner_id IN (SELECT account_id FROM player WHERE alliance_id = ' . $player->getAllianceID() . ' AND game_id = ' . $player->getGameID() . ')');
	if ($db->nextRecord()) {
		$result[] = 'There is a total of ' . number_format($db->getField('total_credits')) . ' credits on the planets';
		$result[] = 'and ' . number_format($db->getField('total_bonds')) . ' credits in bonds.';
	}

	return $result;
}
?>
