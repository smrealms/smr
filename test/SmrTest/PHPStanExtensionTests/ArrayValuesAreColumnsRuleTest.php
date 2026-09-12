<?php declare(strict_types=1);

namespace SmrTest\PHPStanExtensionTests;

use Override;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use SmrTest\PHPStanExtensions\ArrayValuesAreColumnsRule;
use SmrTest\PHPStanExtensions\CallArgumentResolver;

/**
 * @extends RuleTestCase<ArrayValuesAreColumnsRule>
 */
#[CoversClass(ArrayValuesAreColumnsRule::class)]
#[CoversClass(CallArgumentResolver::class)]
class ArrayValuesAreColumnsRuleTest extends RuleTestCase {

	protected function getRule(): Rule {
		return self::getContainer()->getByType(ArrayValuesAreColumnsRule::class);
	}

	/**
	 * @return list<string>
	 */
	#[Override]
	public static function getAdditionalConfigFiles(): array {
		return [__DIR__ . '/config/phpstan.neon'];
	}

	public function test_select_validates_table_and_array_arguments(): void {
		$this->analyse([__DIR__ . '/fixtures/ArrayValuesAreColumnsRule/validation-errors.php'], [
			[
				'Query error: Table "unknown_table" does not exist',
				8,
			],
			[
				'Query error: Column "player.unknown_column" does not exist',
				12,
			],
			[
				'Query error: Column "player.unknown_column" does not exist',
				16,
			],
			[
				'Argument #0 expects a constant string, got string',
				20,
			],
			[
				'Query error: Argument #2 is not a constant array, got array',
				24,
			],
			[
				'Query error: Argument #3 is not a constant array, got array',
				28,
			],
			[
				'Query error: Element #1 of argument #2 must have a string value, got 42',
				32,
			],
			[
				'Query error: Element #0 of argument #3 must have a string value, got 42',
				36,
			],
		]);
	}

	public function test_select_valid_calls(): void {
		$this->analyse([__DIR__ . '/fixtures/ArrayValuesAreColumnsRule/valid-calls.php'], []);
	}

}
