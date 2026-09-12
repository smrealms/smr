<?php declare(strict_types=1);

namespace SmrTest\PHPStanExtensionFixtures\DatabaseReadDynamicReturnTypeExtension;

use Smr\Database;
use function PHPStan\Testing\assertType;

function resolvableRead(Database $db): void {
	$result = $db->read('SELECT player_id FROM player');
	assertType('Smr\\DatabaseResult<Doctrine\\DBAL\\Result>', $result);
	assertType('int<0, 4294967295>', $result->record()->getInt('player_id'));
}

function unresolvableRead(Database $db, string $query): void {
	$result = $db->read($query);
	assertType('Smr\\DatabaseResult', $result);
	assertType('Smr\\DatabaseRecord', $result->record());
}
