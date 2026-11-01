<?php declare(strict_types=1);

namespace SmrTest\lib;

use Exception;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversNamespace;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use Smr\ArrayValue;
use Smr\TypeHelpers\ArrayType;
use Smr\TypeHelpers\BoolType;
use Smr\TypeHelpers\FloatType;
use Smr\TypeHelpers\IntType;
use Smr\TypeHelpers\StringType;
use Smr\TypeHelpers\Type;
use stdClass;

#[CoversClass(ArrayValue::class)]
#[CoversNamespace('Smr\\TypeHelpers')]
class ArrayValueTest extends TestCase {

	public function test_getOptional_returns_typed_value(): void {
		$value = ArrayValue::getOptional(['name' => 'SMR'], 'name', Type::string());
		self::assertSame('SMR', $value);
	}

	public function test_getOptional_throws_for_invalid_value(): void {
		$this->expectException(Exception::class);
		$this->expectExceptionMessage('Array key [name] must be a string, got int: 1');
		ArrayValue::getOptional(['name' => 1], 'name', Type::string());
	}

	public function test_getOptional_returns_null_for_missing_key(): void {
		$value = ArrayValue::getOptional([], 'name', Type::string());
		self::assertNull($value);
	}

	public function test_get_throws_for_missing_key(): void {
		$this->expectException(Exception::class);
		$this->expectExceptionMessage('Could not find expected key in array: name');
		ArrayValue::get([], 'name', Type::string());
	}

	public function test_getOrDefault_returns_default_for_missing_key(): void {
		$value = ArrayValue::getOrDefault([], 'enabled', Type::bool(), false);
		self::assertFalse($value);
	}

	public function test_getOrDefault_returns_null_for_missing_key_with_null_default(): void {
		$value = ArrayValue::getOrDefault([], 'enabled', Type::bool(), null);
		self::assertNull($value);
	}

	/**
	 * @param Type<array<array-key, mixed>|bool|float|int|string> $type
	 */
	#[TestWith([new StringType(), 'SMR', true])]
	#[TestWith([new StringType(), 1, false])]
	#[TestWith([new IntType(), 1, true])]
	#[TestWith([new IntType(), 1.0, false])]
	#[TestWith([new FloatType(), 1.0, true])]
	#[TestWith([new FloatType(), 1, false])]
	#[TestWith([new BoolType(), false, true])]
	#[TestWith([new BoolType(), 0, false])]
	#[TestWith([new ArrayType(), [], true])]
	#[TestWith([new ArrayType(), 'not an array', false])]
	#[TestWith([new ArrayType(new StringType(), new IntType()), [1 => 'SMR'], true])]
	#[TestWith([new ArrayType(new StringType(), new IntType()), [1 => 1], false])]
	#[TestWith([new ArrayType(new StringType(), new IntType()), ['name' => 'SMR'], false])]
	#[TestWith([new ArrayType(new IntType(), new StringType()), ['SMR' => 1], true])]
	public function test_accepts_values(Type $type, mixed $value, bool $matches): void {
		self::assertSame($matches, $type->accepts($value));
	}

	public function test_name_returns_type_name(): void {
		self::assertSame('array', Type::array()->name());
		self::assertSame('array<array-key, string>', Type::array(valueType: Type::string())->name());
		self::assertSame('array<int, mixed>', Type::array(keyType: Type::int())->name());
		self::assertSame(
			'array<int, string>',
			Type::array(valueType: Type::string(), keyType: Type::int())->name(),
		);
		self::assertSame('string', Type::string()->name());
		self::assertSame('int', Type::int()->name());
		self::assertSame('float', Type::float()->name());
		self::assertSame('bool', Type::bool()->name());
	}

	public function test_getOptional_returns_instance_of_requested_class(): void {
		$value = ArrayValue::getOptional(['object' => new stdClass()], 'object', Type::object(stdClass::class));
		self::assertInstanceOf(stdClass::class, $value);
	}

}
