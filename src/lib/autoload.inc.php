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
			require_once(LIB . 'Default/SmrGame.php');
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
	$className = str_replace('\\', DIRECTORY_SEPARATOR, $className);
	// Fallback to Default directory
	$dirs = array_unique([get_game_dir(), 'Default/']);
	foreach ($dirs as $dir) {
		$classFile = LIB . $dir . $className . '.php';
		if (is_file($classFile)) {
			require($classFile);
			return;
		}
	}
}
