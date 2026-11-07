<?php declare(strict_types=1);

namespace SmrTest\lib;

use PHPUnit\Framework\Attributes\CoversClass;
use Smr\Database;
use Smr\WordFilterCache;
use SmrTest\BaseIntegrationSpec;

#[CoversClass(WordFilterCache::class)]
class WordFilterCacheTest extends BaseIntegrationSpec {

	public function test_getInstance_returns_container_managed_instance(): void {
		self::assertSame(WordFilterCache::getInstance(), WordFilterCache::getInstance());
	}

	public function test_filter(): void {
		$db = Database::getInstance();
		$db->insert('word_filter', [
			'word_value' => 'source',
			'word_replacement' => 'result',
		]);
		$cache = new WordFilterCache();

		// Case-insensitive replacement
		self::assertSame('result', $cache->filter('SoUrCe'));

		// Partial word replacement
		self::assertSame('prefix result suffix', $cache->filter('prefix source suffix'));

		// If no match, the original string is returned
		self::assertSame('no-match', $cache->filter('no-match'));
	}

}
