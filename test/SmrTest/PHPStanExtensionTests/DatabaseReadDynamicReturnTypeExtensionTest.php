<?php declare(strict_types=1);

namespace SmrTest\PHPStanExtensionTests;

use Override;
use PHPStan\Testing\TypeInferenceTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use SmrTest\PHPStanExtensions\DatabaseReadDynamicReturnTypeExtension;
use SmrTest\PHPStanExtensions\DatabaseResultObjectType;

#[CoversClass(DatabaseReadDynamicReturnTypeExtension::class)]
#[CoversClass(DatabaseResultObjectType::class)]
class DatabaseReadDynamicReturnTypeExtensionTest extends TypeInferenceTestCase {

	public function test_read_infers_resolvable_and_unresolvable_query_results(): void {
		foreach ($this->gatherAssertTypes(__DIR__ . '/fixtures/DatabaseReadDynamicReturnTypeExtension/types.php') as $assert) {
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
