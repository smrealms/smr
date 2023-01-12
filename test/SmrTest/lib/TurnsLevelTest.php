<?php declare(strict_types=1);

namespace SmrTest\lib;

use PHPUnit\Framework\TestCase;
use Smr\TurnsLevel;

/**
 * @covers Smr\TurnsLevel
 */
class TurnsLevelTest extends TestCase {

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

	public function test_color(): void {
		self::assertSame('red', TurnsLevel::None->color());
		self::assertSame('red', TurnsLevel::Low->color());
		self::assertSame('yellow', TurnsLevel::Medium->color());
		self::assertSame('green', TurnsLevel::High->color());
	}

	public function test_message(): void {
		// Test that each case has valid display HTML
		foreach (TurnsLevel::cases() as $turnsLevel) {
			// We need to wrap in body since messages are just HTML fragments
			simplexml_load_string('<body>' . $turnsLevel->message() . '</body>');
			self::assertSame([], libxml_get_errors(), var_export($turnsLevel, true));
			// Clear errors for the next case
			libxml_clear_errors();
		}
	}

}
