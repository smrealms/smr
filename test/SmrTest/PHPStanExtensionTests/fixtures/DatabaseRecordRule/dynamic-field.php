<?php declare(strict_types=1);

namespace SmrTest\PHPStanExtensionTests\Fixtures\DatabaseRecordRule;

use Smr\Database;

function dynamicField(Database $db, string $field): void {
	$record = $db->select(
		table: 'player',
		returnColumns: ['player_id'],
	)->record();
	$record->getInt($field);
}
