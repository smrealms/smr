<?php declare(strict_types=1);

namespace SmrTest\lib;

use Exception;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Smr\AssetUrlCache;

#[CoversClass(AssetUrlCache::class)]
class AssetUrlCacheTest extends TestCase {

	public function test_getInstance_returns_container_managed_instance(): void {
		self::assertSame(AssetUrlCache::getInstance(), AssetUrlCache::getInstance());
	}

	public function test_resolve_returns_hashed_url(): void {
		$cache = new AssetUrlCache();

		$assetPath = $cache->resolve('/js/smr15.js');
		self::assertMatchesRegularExpression('#^/assets/js/smr15-[A-Z0-9]+\\.js$#', $assetPath);
	}

	public function test_resolve_rejects_unknown_url(): void {
		$cache = new AssetUrlCache();

		$this->expectException(Exception::class);
		$this->expectExceptionMessageIs('Asset not in manifest: /js/does-not-exist.js');
		$cache->resolve('/js/does-not-exist.js');
	}

}
