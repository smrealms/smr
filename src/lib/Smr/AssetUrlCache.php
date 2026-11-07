<?php declare(strict_types=1);

namespace Smr;

use Exception;
use Smr\Container\DiContainer;

final class AssetUrlCache {

	/** @var array<string, string> */
	private array $manifest = [];

	public function __construct() {
		$json = file_get_contents(ASSET_MANIFEST);
		assert($json !== false);
		$decodedManifest = json_decode($json, true, flags: JSON_THROW_ON_ERROR);
		if (!is_array($decodedManifest)) {
			throw new Exception('Asset manifest must be an object');
		}

		foreach ($decodedManifest as $source => $hashed) {
			if (!is_string($source) || !is_string($hashed)) {
				throw new Exception('Asset manifest must map paths to paths');
			}
			$this->manifest[$source] = $hashed;
		}
	}

	public static function getInstance(): self {
		return DiContainer::getClass(self::class);
	}

	public function resolve(string $url): string {
		$value = $this->manifest[$url] ?? null;
		if ($value === null) {
			throw new Exception('Asset not in manifest: ' . $url);
		}
		return $value;
	}

}
