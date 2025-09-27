<?php declare(strict_types=1);

namespace SmrTest\lib\functions;

use PHPUnit\Framework\Attributes\CoversFunction;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

#[CoversFunction('create_submit')]
#[CoversFunction('create_submit_display')]
class CreateSubmitTest extends TestCase {

	private static bool $original_libxml_use_internal_errors;

	public static function setUpBeforeClass(): void {
		// Get the original libxml state so we can restore it later
		self::$original_libxml_use_internal_errors = libxml_use_internal_errors(true);
		libxml_clear_errors();
	}

	public static function tearDownAfterClass(): void {
		// Restore the original libxml state
		libxml_use_internal_errors(self::$original_libxml_use_internal_errors);
	}

	/**
	 * @param array<string, string | int | true> $fields
	 */
	#[TestWith([null, [], '<button name="foo" value="bar">bar</button>'])]
	#[TestWith(['Bar', [], '<button name="foo" value="bar">Bar</button>'])]
	#[TestWith([null, ['key' => 'val'], '<button name="foo" value="bar" key="val">bar</button>'])]
	#[TestWith([null, ['key' => 3], '<button name="foo" value="bar" key="3">bar</button>'])]
	#[TestWith([null, ['disabled' => true], '<button name="foo" value="bar" disabled="true">bar</button>'])]
	public function test_create_submit(?string $display, array $fields, string $expected): void {
		// Check that the output matches the expected output
		$result = create_submit('foo', 'bar', $display, $fields);
		self::assertSame($result, $expected);

		// Test that the result is valid display HTML
		simplexml_load_string($result);
		self::assertSame([], libxml_get_errors());
		// Clear errors for the next case
		libxml_clear_errors();
	}

	/**
	 * @param array<string, string | int | true> $fields
	 */
	#[TestWith([[], '<input type="submit" value="Foo" />'])]
	#[TestWith([['key' => 'val'], '<input type="submit" value="Foo" key="val" />'])]
	#[TestWith([['key' => 3], '<input type="submit" value="Foo" key="3" />'])]
	#[TestWith([['disabled' => true], '<input type="submit" value="Foo" disabled="true" />'])]
	public function test_create_submit_display(array $fields, string $expected): void {
		// Check that the output matches the expected output
		$result = create_submit_display('Foo', $fields);
		self::assertSame($result, $expected);

		// Test that the result is valid display HTML
		simplexml_load_string($result);
		self::assertSame([], libxml_get_errors());
		// Clear errors for the next case
		libxml_clear_errors();
	}

}
