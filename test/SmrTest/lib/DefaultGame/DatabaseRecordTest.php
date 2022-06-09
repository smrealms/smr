<?php declare(strict_types=1);

namespace SmrTest\lib\DefaultGame;

use ArrayObject;
use Exception;
use PHPUnit\Framework\TestCase;
use Smr\DatabaseRecord;
use TypeError;
use UnhandledMatchError;

/**
 * @covers \Smr\DatabaseRecord
 */
class DatabaseRecordTest extends TestCase {

	public function test_hasField(): void {
		// Construct a record that has the field 'foo', but not 'bla'
		$record = new DatabaseRecord(['name' => 'value']);
		self::assertTrue($record->hasField('name'));
		self::assertFalse($record->hasField('does_not_exist'));
	}

	//------------------------------------------------------------------------

	public function test_getField(): void {
		// Construct a record with a string value
		$record = new DatabaseRecord(['name' => 'value_string']);
		self::assertSame('value_string', $record->getField('name'));
	}

	public function test_getField_with_null_value(): void {
		// Construct a record with a null value
		$record = new DatabaseRecord(['name' => null]);
		self::assertSame(null, $record->getField('name'));
	}

	//------------------------------------------------------------------------

	public function test_getString(): void {
		// Construct a record with a string value
		$record = new DatabaseRecord(['name' => 'value_string']);
		self::assertSame('value_string', $record->getString('name'));
	}

	public function test_getString_with_null_value(): void {
		// Construct a record with a null value
		$record = new DatabaseRecord(['name' => null]);
		$this->expectException(TypeError::class);
		$record->getString('name');
	}

	//------------------------------------------------------------------------

	public function test_getBoolean(): void {
		$record = new DatabaseRecord([
			'name_true' => 'TRUE',
			'name_false' => 'FALSE',
		]);
		self::assertSame(true, $record->getBoolean('name_true'));
		self::assertSame(false, $record->getBoolean('name_false'));
	}

	public function test_getBoolean_with_non_boolean_field(): void {
		$record = new DatabaseRecord(['name' => 'NONBOOLEAN']);
		$this->expectException(UnhandledMatchError::class);
		$record->getBoolean('name');
	}

	//------------------------------------------------------------------------

	public function test_getInt(): void {
		$record = new DatabaseRecord(['name' => '3']);
		self::assertSame(3, $record->getInt('name'));
	}

	/**
	 * @dataProvider provider_getInt_with_non_int_field
	 */
	public function test_getInt_with_non_int_field(mixed $value, string $error): void {
		$record = new DatabaseRecord(['name' => $value]);
		$this->expectException(Exception::class);
		$this->expectExceptionMessage($error);
		$record->getInt('name');
	}

	/**
	 * @return array<array{?string, string}>
	 */
	public function provider_getInt_with_non_int_field(): array {
		return [
			['1a', 'Failed to convert \'1a\' to int'],
			['3.14', 'Failed to convert \'3.14\' to int'],
			[null, 'Failed to convert NULL to int'],
		];
	}

	//------------------------------------------------------------------------

	public function test_getFloat(): void {
		$record = new DatabaseRecord(['name' => '3.14']);
		self::assertSame(3.14, $record->getFloat('name'));
	}

	/**
	 * @dataProvider provider_getFloat_with_non_float_field
	 */
	public function test_getFloat_with_non_float_field(mixed $value, string $error): void {
		$record = new DatabaseRecord(['name' => $value]);
		$this->expectException(Exception::class);
		$this->expectExceptionMessage($error);
		$record->getFloat('name');
	}

	/**
	 * @return array<array{?string, string}>
	 */
	public function provider_getFloat_with_non_float_field(): array {
		return [
			['1a', 'Failed to convert \'1a\' to float'],
			[null, 'Failed to convert NULL to float'],
		];
	}

	//------------------------------------------------------------------------

	public function test_getObject(): void {
		// Construct a record with various types of objects
		$record = new DatabaseRecord([
			'name' => serialize(new ArrayObject(['a' => 1, 'b' => 2])),
			'name_null' => null,
			'name_compressed' => gzcompress(serialize(['c', 'd'])),
		]);
		// Class objects must be compared here with Equals instead of Same
		// since the two objects will not be the same instance.
		self::assertEquals(new ArrayObject(['a' => 1, 'b' => 2]), $record->getObject('name'));
		self::assertSame(null, $record->getObject('name_null', nullable: true));
		self::assertSame(['c', 'd'], $record->getObject('name_compressed', compressed: true));
	}

	public function test_getRow(): void {
		// getRow returns the entire record
		$record = new DatabaseRecord(['name1' => '1', 'name2' => '2']);
		self::assertSame(['name1' => '1', 'name2' => '2'], $record->getRow());
	}

}
