<?php declare(strict_types=1);

namespace SmrTest\PHPStanExtensionTests\Fixtures\ArrayValuesAreColumnsRule;

use Smr\Database;

function positionalArguments(Database $db): void {
	$db->select('player', [], ['player_id']);
}

function reorderedNamedArguments(Database $db): void {
	$db->select(
		returnColumns: ['player_id'],
		table: 'player',
	);
}

function escapedTableAndColumnNames(Database $db): void {
	$db->select(
		table: '`player`',
		returnColumns: ['`player_id`'],
		orderBy: ['`player_name`'],
	);
}

function criteriaValuesAreNotColumnNames(Database $db): void {
	$db->select(
		table: 'player',
		criteria: ['player_id' => 42],
		returnColumns: ['player_id'],
	);
}

function allReturnColumns(Database $db): void {
	$db->select(
		table: 'player',
		returnColumns: ['*'],
	);
}

function omittedReturnColumns(Database $db): void {
	// orderBy is after returnColumns in select()'s signature. PHPStan fills the
	// omitted returnColumns slot with its ['*'] default while normalizing this.
	$db->select('player', orderBy: ['player_id']);
}
