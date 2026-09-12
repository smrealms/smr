<?php declare(strict_types=1);

namespace SmrTest\PHPStanExtensionTests;

use Override;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use SmrTest\PHPStanExtensions\DatabaseRecordRule;
use SmrTest\PHPStanExtensions\DatabaseRecordTypeResolver;

/**
 * @extends RuleTestCase<DatabaseRecordRule>
 */
#[CoversClass(DatabaseRecordRule::class)]
#[CoversClass(DatabaseRecordTypeResolver::class)]
class DatabaseRecordRuleTest extends RuleTestCase {

	protected function getRule(): Rule {
		return self::getContainer()->getByType(DatabaseRecordRule::class);
	}

	/**
	 * @return list<string>
	 */
	#[Override]
	public static function getAdditionalConfigFiles(): array {
		return [__DIR__ . '/config/phpstan.neon'];
	}

	public function test_getInt_reports_missing_field(): void {
		$this->analyse([__DIR__ . '/fixtures/DatabaseRecordRule/missing-field.php'], [
			[
				'Smr\\DatabaseRecord does not contain field: player_name',
				12,
			],
		]);
	}

	public function test_getInt_reports_incompatible_field_type(): void {
		$this->analyse([__DIR__ . '/fixtures/DatabaseRecordRule/incompatible-field-type.php'], [
			[
				'Smr\\DatabaseRecord::getInt returns type int, but field `player_name` has type string',
				12,
			],
		]);
	}

	public function test_getInt_reports_dynamic_field_name(): void {
		$this->analyse([__DIR__ . '/fixtures/DatabaseRecordRule/dynamic-field.php'], [
			[
				'Smr\\DatabaseRecord field name must be a constant string, got string',
				12,
			],
		]);
	}

}
