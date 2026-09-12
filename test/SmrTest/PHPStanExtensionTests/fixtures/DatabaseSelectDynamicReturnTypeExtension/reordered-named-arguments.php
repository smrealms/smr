<?php declare(strict_types=1);

namespace SmrTest\PHPStanExtensionTests\Fixtures\DatabaseSelectDynamicReturnTypeExtension;

use Smr\Database;
use function PHPStan\Testing\assertType;

function reorderedNamedArguments(Database $db): void {
	$record = $db->select(
		returnColumns: ['player_id'],
		table: 'player',
	)->record();
	assertType('int<0, 4294967295>', $record->getInt('player_id'));
}
