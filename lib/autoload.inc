<?php

/**
 * Returns the correct file directory based on `$overrideGameID`.
 * Uses a "Default" directory if no global override exists.
 * NOTE: If a class is used before $overrideGameID is defined,
 * it will include the wrong version of the class.
 */
function get_game_dir() {
	global $overrideGameID;
	static $storedDir;
	if (isset($storedDir)) {
		$gameDir = $storedDir;
	} else {
		if ($overrideGameID > 0) {
			require_once(LIB . 'Default/SmrGame.class.inc');
			$storedDir = SmrGame::getGame($overrideGameID)->getGameType() . '/';
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
function get_class_loc($className) {
	$classFile = LIB . get_game_dir() . $className . '.class.inc';
	if (!is_file($classFile)) {
		// Fallback to Default directory
		$classFile = LIB . 'Default/' . $className . '.class.inc';
	}
	require($classFile);
}

/**
 * Includes the correct game-specific version of a non-class file.
 * Caches the result to reduce the expense of multiple calls for the same file.
 * Try to avoid calling this before `$overrideGameID` is set!
 */
function get_file_loc($fileName) {
	$gameDir = get_game_dir();

	static $cache = array();
	$cacheKey = $gameDir . $fileName;
	if (isset($cache[$cacheKey])) {
		return $cache[$cacheKey];
	}

	$startPaths = array(ENGINE, LIB, ADMIN);
	$midPaths = array($gameDir, 'Default/');
	foreach ($midPaths as $midPath) {
		foreach ($startPaths as $startPath) {
			$filePath = $startPath . $midPath . $fileName;
			if (is_file($filePath) && is_readable($filePath)) {
				$cache[$cacheKey] = $filePath;
				return $filePath;
			}
		}
	}
	$filePath = WWW . $fileName;
	if (is_file($filePath) && is_readable($filePath)) {
		$cache[$cacheKey] = $filePath;
		return WWW . $fileName;
	}

	//We haven't matched on anything
	throw new Exception('Cannot match given filename: ' . $fileName);
}
