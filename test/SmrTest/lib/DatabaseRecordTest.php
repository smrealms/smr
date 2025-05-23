<?php declare(strict_types=1);

namespace SmrTest\lib;

use ArrayObject;
use Exception;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use Smr\BountyType;
use Smr\DatabaseRecord;
use Smr\ShipClass;
use TypeError;

#[CoversClass(DatabaseRecord::class)]
class DatabaseRecordTest extends TestCase {

	public function test_getNullableString(): void {
		// Construct a record with a string value
		$record = new DatabaseRecord(['name' => 'value_string']);
		self::assertSame('value_string', $record->getNullableString('name'));
	}

	public function test_getNullableString_with_null_value(): void {
		// Construct a record with a null value
		$record = new DatabaseRecord(['name' => null]);
		self::assertSame(null, $record->getNullableString('name'));
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
		$this->expectExceptionMessage('Return value must be of type string, null returned');
		/** @phpstan-ignore-next-line: method.resultUnused */
		$record->getString('name');
	}

	//------------------------------------------------------------------------

	public function test_getNullableBoolean(): void {
		$record = new DatabaseRecord([
			'name_true' => 'TRUE',
			'name_false' => 'FALSE',
		]);
		self::assertSame(true, $record->getNullableBoolean('name_true'));
		self::assertSame(false, $record->getNullableBoolean('name_false'));
	}

	public function test_getNullableBoolean_with_null_value(): void {
		$record = new DatabaseRecord(['name' => null]);
		self::assertSame(null, $record->getNullableBoolean('name'));
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
		$this->expectException(Exception::class);
		$this->expectExceptionMessage('Unexpected boolean record: NONBOOLEAN');
		$record->getBoolean('name');
	}

	//------------------------------------------------------------------------

	public function test_getNullableInt(): void {
		// Construct a record with an int value
		$record = new DatabaseRecord(['name' => '3']);
		self::assertSame(3, $record->getNullableInt('name'));
	}

	public function test_getNullableInt_with_null_value(): void {
		// Construct a record with a null value
		$record = new DatabaseRecord(['name' => null]);
		self::assertSame(null, $record->getNullableInt('name'));
	}

	//------------------------------------------------------------------------

	public function test_getInt(): void {
		$record = new DatabaseRecord(['name' => '3']);
		self::assertSame(3, $record->getInt('name'));
	}

	#[TestWith(['1a', 'Failed to convert \'1a\' to int'])]
	#[TestWith(['3.14', 'Failed to convert \'3.14\' to int'])]
	#[TestWith([null, 'Failed to convert NULL to int'])]
	public function test_getInt_with_non_int_field(mixed $value, string $error): void {
		$record = new DatabaseRecord(['name' => $value]);
		$this->expectException(Exception::class);
		$this->expectExceptionMessage($error);
		$record->getInt('name');
	}

	//------------------------------------------------------------------------

	public function test_getFloat(): void {
		$record = new DatabaseRecord(['name' => '3.14']);
		self::assertSame(3.14, $record->getFloat('name'));
	}

	#[TestWith(['1a', 'Failed to convert \'1a\' to float'])]
	#[TestWith([null, 'Failed to convert NULL to float'])]
	public function test_getFloat_with_non_float_field(mixed $value, string $error): void {
		$record = new DatabaseRecord(['name' => $value]);
		$this->expectException(Exception::class);
		$this->expectExceptionMessage($error);
		$record->getFloat('name');
	}

	//------------------------------------------------------------------------

	#[TestWith([true])]
	#[TestWith([false])]
	public function test_getObject(bool $compressed): void {
		// Construct a record with various array objects
		$expected = ['a' => 1, 'b' => 2];
		$value = serialize($expected);
		if ($compressed) {
			$value = gzcompress($value);
		}
		$record = new DatabaseRecord(['name' => $value]);
		self::assertSame($expected, $record->getObject('name', $compressed));
	}

	public function test_getNullableObject(): void {
		$record = new DatabaseRecord([
			'null' => null,
			'not_null' => serialize([1, 2]),
		]);
		self::assertSame(null, $record->getNullableObject('null'));
		self::assertSame([1, 2], $record->getNullableObject('not_null'));
	}

	#[TestWith([true])]
	#[TestWith([false])]
	public function test_getClass(bool $compressed): void {
		// Construct a record with a class instance
		$expected = new ArrayObject(['a' => 1, 'b' => 2]);
		$value = serialize($expected);
		if ($compressed) {
			$value = gzcompress($value);
		}
		$record = new DatabaseRecord(['name' => $value]);
		// Class instances must be compared here with Equals instead of Same
		// since the two objects will not be the same instance in memory.
		self::assertEquals($expected, $record->getClass('name', ArrayObject::class, $compressed));
	}

	public function test_getClass_error(): void {
		// Construct a record with a string, but then fetch as ArrayObject
		$record = new DatabaseRecord(['name' => serialize('foo')]);
		$this->expectException(Exception::class);
		$this->expectExceptionMessage('Value \'foo\' is not of type ArrayObject');
		$record->getClass('name', ArrayObject::class);
	}

	//------------------------------------------------------------------------

	public function test_getIntEnum(): void {
		$record = new DatabaseRecord([
			'ship_class' => 2,
		]);
		self::assertSame(ShipClass::Trader, $record->getIntEnum('ship_class', ShipClass::class));
	}

	public function test_getStringEnum(): void {
		$record = new DatabaseRecord([
			'bounty_type' => 'HQ',
		]);
		self::assertSame(BountyType::HQ, $record->getStringEnum('bounty_type', BountyType::class));
	}

	//------------------------------------------------------------------------

	public function test_getRow(): void {
		// getRow returns the entire record
		$record = new DatabaseRecord(['name1' => '1', 'name2' => '2']);
		self::assertSame(['name1' => '1', 'name2' => '2'], $record->getRow());
	}

}
