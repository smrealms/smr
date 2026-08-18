<?php declare(strict_types=1);

namespace SmrTest\lib\functions;

use Exception;
use PHPUnit\Framework\Attributes\CoversFunction;
use PHPUnit\Framework\TestCase;

#[CoversFunction('asset_url')]
class AssetUrlTest extends TestCase {

	private string|false $assetMode;

	protected function setUp(): void {
		$this->assetMode = getenv('SMR_DEV_ASSETS');
	}

	protected function tearDown(): void {
		// Restore original environment variable value
		putenv('SMR_DEV_ASSETS' . ($this->assetMode === false ? '' : '=' . $this->assetMode));
	}

	public function test_source_asset(): void {
		$sourcePath = '/js/smr15.js';
		putenv('SMR_DEV_ASSETS=true');

		self::assertFileExists(WWW . $sourcePath);
		self::assertSame($sourcePath, asset_url($sourcePath));
	}

	public function test_hashed_asset(): void {
		$sourcePath = '/js/smr15.js';
		putenv('SMR_DEV_ASSETS'); // unset

		$assetPath = asset_url($sourcePath);
		self::assertMatchesRegularExpression('#^/assets/js/smr15-[A-Z0-9]+\\.js$#', $assetPath);
		self::assertFileExists(ROOT . $assetPath);
	}

	public function test_missing_asset(): void {
		$sourcePath = '/js/does-not-exist.js';

		self::assertFileDoesNotExist(WWW . $sourcePath);
		putenv('SMR_DEV_ASSETS=true');
		self::assertSame($sourcePath, asset_url($sourcePath));

		putenv('SMR_DEV_ASSETS'); // unset
		$this->expectException(Exception::class);
		$this->expectExceptionMessage('Asset not in manifest: /js/does-not-exist.js');
		asset_url($sourcePath);
	}

}
