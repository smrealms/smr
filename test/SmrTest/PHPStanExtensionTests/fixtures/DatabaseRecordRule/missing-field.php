<?php declare(strict_types=1);

namespace SmrTest\PHPStanExtensionFixtures\DatabaseRecordRule;

use Smr\Database;

function missingField(Database $db): void {
	$record = $db->select(
		table: 'player',
		returnColumns: ['player_id'],
	)->record();
	$record->getInt('player_name');
}
