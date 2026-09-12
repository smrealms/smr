<?php declare(strict_types=1);

namespace SmrTest\PHPStanExtensionFixtures\DatabaseRecordDynamicReturnTypeExtension;

use Smr\Database;
use function PHPStan\Testing\assertType;

function getters(Database $db): void {
	$record = $db->select(
		table: 'player',
		returnColumns: ['player_id'],
	)->record();

	assertType('int<0, 4294967295>', $record->getInt('player_id'));
	assertType('int<0, 4294967295>', $record->getInt(name: 'player_id'));

	// Fallback to docstring return type because DatabaseRecord RTE emits the
	// error that the field is not present in the record.
	assertType('int', $record->getInt('player_name'));

	// Check that we get all fields when none are specified (default '*')
	$allFieldsRecord = $db->select(table: 'player')->record();
	assertType('int<0, 4294967295>', $allFieldsRecord->getInt('player_id'));

	// Fallback to docstring return type because getRow is not supported
	assertType('array<string, mixed>', $record->getRow());

	// Fallback to docstring return type because DatabaseRecord RTE emits the
	// error that the field doesn't have the type suggested by the getter.
	$stringRecord = $db->select(
		table: 'player',
		returnColumns: ['player_name'],
	)->record();
	assertType('int', $stringRecord->getInt('player_name'));
	assertType('bool', $stringRecord->getBoolean('player_name'));

	// PDO returns aggregate counts as numeric strings; getInt() converts them.
	$numericStringRecord = $db
		->read('SELECT COUNT(*) AS player_count FROM player')
		->record();
	assertType('int<0, max>', $numericStringRecord->getInt('player_count'));
}

function dynamicField(Database $db, string $field): void {
	// DatabaseRecordRule reports that the field cannot be checked against the row shape.
	$record = $db->select(
		table: 'player',
		returnColumns: ['player_id'],
	)->record();
	assertType('int', $record->getInt($field));
}

function unionOfConstantFieldNames(Database $db, bool $usePlayerId): void {
	$record = $db->select(
		table: 'player',
		returnColumns: ['player_id', 'account_id'],
	)->record();
	$field = $usePlayerId ? 'player_id' : 'account_id';
	assertType('int<0, 4294967295>', $record->getInt($field));
}
