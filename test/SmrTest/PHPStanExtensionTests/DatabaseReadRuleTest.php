<?php declare(strict_types=1);

namespace SmrTest\PHPStanExtensionTests;

use Override;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use SmrTest\PHPStanExtensions\DatabaseReadDynamicReturnTypeExtension;
use staabm\PHPStanDba\Rules\SyntaxErrorInPreparedStatementMethodRule;

/**
 * The return type extension falls back to DatabaseResult for a dynamic query.
 * Packagist phpstan-dba leaves such whole-query strings to wrapper layers in
 * normal mode; phpstan-dba's own suite covers its debug-mode reporting.
 *
 * @extends RuleTestCase<SyntaxErrorInPreparedStatementMethodRule>
 */
#[CoversClass(DatabaseReadDynamicReturnTypeExtension::class)]
class DatabaseReadRuleTest extends RuleTestCase {

	protected function getRule(): Rule {
		return self::getContainer()->getByType(SyntaxErrorInPreparedStatementMethodRule::class);
	}

	/**
	 * @return list<string>
	 */
	#[Override]
	public static function getAdditionalConfigFiles(): array {
		return [__DIR__ . '/config/phpstan.neon'];
	}

	public function test_read_allows_dynamic_query_in_normal_mode(): void {
		// NOTE: It would be helpful if phpstan-dba would emit an unresolvable
		// query error in debug mode in the case of a non-constant query string.
		$this->analyse([__DIR__ . '/fixtures/DatabaseReadRule/dynamic-query.php'], []);
	}

	public function test_read_accepts_value_from_invalid_record_getter(): void {
		$this->analyse([__DIR__ . '/fixtures/DatabaseReadRule/invalid-record-getter-value.php'], []);
	}

}
