<?php declare(strict_types=1);

namespace SmrTest\PHPStanExtensionFixtures\DatabaseResultDynamicReturnTypeExtension;

use Smr\Database;
use function PHPStan\Testing\assertType;

function record(Database $db): void {
	$record = $db->read('SELECT player_id FROM player')->record();
	assertType('Smr\DatabaseRecord<array{player_id: int<0, 4294967295>}>', $record);
}

function records(Database $db): void {
	$records = $db->read('SELECT player_id FROM player')->records();
	assertType('Generator<int, Smr\DatabaseRecord<array{player_id: int<0, 4294967295>}>>', $records);
	foreach ($records as $record) {
		assertType('Smr\DatabaseRecord<array{player_id: int<0, 4294967295>}>', $record);
	}
}
