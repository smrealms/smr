<?php declare(strict_types=1);

namespace SmrTest\PHPStanExtensionTests\Fixtures\DatabaseSelectDynamicReturnTypeExtension;

use Smr\Database;
use function PHPStan\Testing\assertType;

function dynamicReturnColumns(Database $db, array $returnColumns): void {
	$result = $db->select(
		table: 'player',
		returnColumns: $returnColumns,
	);
	assertType('Smr\\DatabaseResult', $result);
}
