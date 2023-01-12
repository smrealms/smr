<?php declare(strict_types=1);

namespace SmrTest\lib;

use PHPUnit\Framework\TestCase;
use Smr\DisplayNameValidator;
use Smr\Exceptions\UserError;

/**
 * @covers Smr\DisplayNameValidator
 */
class DisplayNameValidatorTest extends TestCase {

	/**
	 * @dataProvider invalid_name_provider
	 */
	public function test_invalid_name(string $name, string $exception): void {
		$this->expectException(UserError::class);
		$this->expectExceptionMessage($exception);
		DisplayNameValidator::validate($name);
	}

	/**
	 * @return array<array{string, string}>
	 */
	public function invalid_name_provider(): array {
		return [
			// empty string
			['', 'You must enter a name!'],
			// name containing [NPC]
			['x[NPC]x', 'Names cannot contain "[NPC]".'],
			// extended ascii 128
			['xÇx', 'Names must contain only standard printable characters.'],
			// extended ascii 254
			['x■x', 'Names must contain only standard printable characters.'],
			// ascii control characters
			[chr(0), 'Names must contain only standard printable characters.'],
			[chr(31), 'Names must contain only standard printable characters.'],
			[chr(127), 'Names must contain only standard printable characters.'],
			// 5 or more non-alphanumeric characters
			['0 1 2 3 4 5', 'You cannot use a name with more than 4 special characters.'],
			['x[[+]]x', 'You cannot use a name with more than 4 special characters.'],
		];
	}

	/**
	 * @dataProvider valid_name_provider
	 */
	public function test_valid_name(string $name): void {
		// test that an exception is not thrown
		DisplayNameValidator::validate($name);
		$this->addToAssertionCount(1);
	}

	/**
	 * @return array<array<string>>
	 */
	public function valid_name_provider(): array {
		return [
			// normal alphanumeric
			['aBc123'],
			// fewer than 5 non-alphanumeric characters
			['x[[]]x'],
			['0 1 2 3 4'],
		];
	}

}
