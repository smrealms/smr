<?php declare(strict_types=1);

namespace SmrTest\PHPStanExtensionTests;

use Override;
use PHPStan\Testing\TypeInferenceTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use SmrTest\PHPStanExtensions\CallArgumentResolver;
use SmrTest\PHPStanExtensions\DatabaseSelectDynamicReturnTypeExtension;

#[CoversClass(CallArgumentResolver::class)]
#[CoversClass(DatabaseSelectDynamicReturnTypeExtension::class)]
class DatabaseSelectDynamicReturnTypeExtensionTest extends TypeInferenceTestCase {

	public function test_select_with_dynamic_return_columns_falls_back_to_declared_type(): void {
		// A dynamic column list has no static row shape, but is valid at runtime.
		$asserts = self::gatherAssertTypes(
			__DIR__ . '/fixtures/DatabaseSelectDynamicReturnTypeExtension/dynamic-return-columns.php',
		);
		foreach ($asserts as $assert) {
			$this->assertFileAsserts(...$assert);
		}
	}

	public function test_select_with_reordered_named_arguments_infers_result_type(): void {
		// PHP permits named arguments in source order unrelated to parameter order.
		$asserts = self::gatherAssertTypes(
			__DIR__ . '/fixtures/DatabaseSelectDynamicReturnTypeExtension/reordered-named-arguments.php',
		);
		foreach ($asserts as $assert) {
			$this->assertFileAsserts(...$assert);
		}
	}

	/**
	 * @return list<string>
	 */
	#[Override]
	public static function getAdditionalConfigFiles(): array {
		return [__DIR__ . '/config/phpstan.neon'];
	}

}
