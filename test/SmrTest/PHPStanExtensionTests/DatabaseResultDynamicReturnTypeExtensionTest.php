<?php declare(strict_types=1);

namespace SmrTest\PHPStanExtensionTests;

use Override;
use PHPStan\Testing\TypeInferenceTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use SmrTest\PHPStanExtensions\DatabaseRecordObjectType;
use SmrTest\PHPStanExtensions\DatabaseResultDynamicReturnTypeExtension;

#[CoversClass(DatabaseRecordObjectType::class)]
#[CoversClass(DatabaseResultDynamicReturnTypeExtension::class)]
class DatabaseResultDynamicReturnTypeExtensionTest extends TypeInferenceTestCase {

	public function test_record_and_records_propagate_query_row_types(): void {
		foreach ($this->gatherAssertTypes(__DIR__ . '/fixtures/DatabaseResultDynamicReturnTypeExtension/types.php') as $assert) {
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
