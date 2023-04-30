<?php declare(strict_types=1);

use Smr\AbstractPlayer;
use Smr\Database;

/**
 * @return array<string>
 */
function shared_channel_msg_money(AbstractPlayer $player): array {
	// Insist the player is in an alliance, otherwise this reports data
	// for ALL allianceless (i.e. alliance=0) players.
	if (!$player->hasAlliance()) {
		return ['This command can only be used when you are in an alliance.'];
	}

	$result = [];

	// get money from AA
	$result[] = 'The alliance has ' . number_format($player->getAlliance(true)->getBank()) . ' credits in the bank account.';

	// get money on ships and personal bank accounts
	$db = Database::getInstance();
	$dbResult = $db->read('SELECT IFNULL(SUM(credits), 0) as total_onship, IFNULL(SUM(bank), 0) as total_onbank FROM player WHERE alliance_id = :alliance_id AND game_id = :game_id', [
		'alliance_id' => $player->getAllianceID(),
		'game_id' => $player->getGameID(),
	]);

	$dbRecord = $dbResult->record();
	$result[] = 'Alliance members carry a total of ' . number_format($dbRecord->getInt('total_onship')) . ' credits with them';
	$result[] = 'and keep a total of ' . number_format($dbRecord->getInt('total_onbank')) . ' credits in their personal bank accounts.';

	// get money on planets
	$dbResult = $db->read('SELECT IFNULL(SUM(credits), 0) AS total_credits, IFNULL(SUM(bonds), 0) AS total_bonds FROM planet WHERE game_id = :game_id AND owner_id IN (SELECT account_id FROM player WHERE alliance_id = :alliance_id AND game_id = :game_id)', [
		'alliance_id' => $player->getAllianceID(),
		'game_id' => $player->getGameID(),
	]);
	$dbRecord = $dbResult->record();
	$result[] = 'There is a total of ' . number_format($dbRecord->getInt('total_credits')) . ' credits on the planets';
	$result[] = 'and ' . number_format($dbRecord->getInt('total_bonds')) . ' credits in bonds.';

	return $result;
}
