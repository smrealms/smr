<?php declare(strict_types=1);

/**
 * Returns the correct file directory based on `$overrideGameID`.
 * Uses a "Default" directory if no global override exists.
 * NOTE: If a class is used before $overrideGameID is defined,
 * it will include the wrong version of the class.
 */
function get_game_dir(): string {
	global $overrideGameID;
	static $storedDir;
	if (isset($storedDir)) {
		$gameDir = $storedDir;
	} else {
		if ($overrideGameID > 0) {
			require_once(LIB . 'Default/SmrGame.class.php');
			// Game types can have spaces in them, but their corresponding
			// directories do not.
			$gameType = SmrGame::getGame($overrideGameID)->getGameType();
			$storedDir = str_replace(' ', '', $gameType) . '/';
			$gameDir = $storedDir;
		} else {
			$gameDir = 'Default/';
		}
	}
	return $gameDir;
}

/**
 * This function is registered as the autoloader for classes.
 * Includes the correct game-specific version of a class file.
 * Try to avoid calling this before `$overrideGameID` is set!
 */
function get_class_loc(string $className): void {
	$className = str_replace("\\", DIRECTORY_SEPARATOR, $className);
	$classFile = LIB . get_game_dir() . $className . '.class.php';
	if (!is_file($classFile)) {
		// Fallback to Default directory
		$classFile = LIB . 'Default/' . $className . '.class.php';
	}
	require($classFile);
}

/**
 * Includes the correct game-specific version of a non-class file.
 * Caches the result to reduce the expense of multiple calls for the same file.
 * Try to avoid calling this before `$overrideGameID` is set!
 * Note: This is only intended to be used in Page::process.
 */
function get_file_loc(string $fileName): string {
	$gameDir = get_game_dir();

	static $cache = [];
	$cacheKey = $gameDir . $fileName;
	if (isset($cache[$cacheKey])) {
		return $cache[$cacheKey];
	}

	$gameDirs = array_unique([$gameDir, 'Default/']);
	foreach ($gameDirs as $gameDir) {
		$filePath = ENGINE . $gameDir . $fileName;
		if (is_file($filePath) && is_readable($filePath)) {
			$cache[$cacheKey] = $filePath;
			return $filePath;
		}
	}

	//We haven't matched on anything
	throw new Exception('Cannot match given filename: ' . $fileName);
}
