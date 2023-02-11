<?php declare(strict_types=1);

namespace SmrTest\PHPStanExtensionTests\Fixtures\ArrayValuesAreColumnsRule;

use Smr\Database;

function unknownTable(Database $db): void {
	$db->select('unknown_table');
}

function unknownReturnColumns(Database $db): void {
	$db->select('player', [], ['unknown_column']);
}

function unknownOrderBy(Database $db): void {
	$db->select('player', orderBy: ['unknown_column']);
}

function dynamicTable(Database $db, string $table): void {
	$db->select($table);
}

function dynamicReturnColumns(Database $db, array $returnColumns): void {
	$db->select('player', [], $returnColumns);
}

function dynamicOrderBy(Database $db, array $orderBy): void {
	$db->select('player', [], ['player_id'], $orderBy);
}

function nonStringReturnColumn(Database $db): void {
	$db->select('player', [], ['player_id', 42]);
}

function nonStringOrderBy(Database $db): void {
	$db->select('player', [], ['player_id'], [42]);
}
