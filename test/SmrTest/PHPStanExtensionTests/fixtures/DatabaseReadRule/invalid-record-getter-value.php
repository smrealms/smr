<?php declare(strict_types=1);

namespace SmrTest\PHPStanExtensionFixtures\DatabaseReadRule;

use Smr\Database;

function invalidRecordGetterValue(Database $db): void {
	$record = $db->select(
		table: 'player',
		returnColumns: ['player_name'],
	)->record();

	// DatabaseRecordRule reports the incompatible getter. Its declared int
	// fallback must remain usable as a prepared-statement parameter.
	$db->read('SELECT player_id FROM player WHERE player_id = :player_id', [
		'player_id' => $record->getInt('player_name'),
	]);
}
