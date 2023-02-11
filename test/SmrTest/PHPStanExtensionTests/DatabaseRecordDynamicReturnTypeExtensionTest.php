<?php declare(strict_types=1);

namespace SmrTest\PHPStanExtensionTests;

use Override;
use PHPStan\Testing\TypeInferenceTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use SmrTest\PHPStanExtensions\DatabaseRecordDynamicReturnTypeExtension;
use SmrTest\PHPStanExtensions\DatabaseRecordTypeResolver;

#[CoversClass(DatabaseRecordDynamicReturnTypeExtension::class)]
#[CoversClass(DatabaseRecordTypeResolver::class)]
class DatabaseRecordDynamicReturnTypeExtensionTest extends TypeInferenceTestCase {

	public function test_database_record_getters_infer_row_field_types(): void {
		foreach ($this->gatherAssertTypes(__DIR__ . '/fixtures/DatabaseRecordDynamicReturnTypeExtension/types.php') as $assert) {
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
