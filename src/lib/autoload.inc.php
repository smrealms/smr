<?php declare(strict_types=1);

/**
 * This function is registered as the autoloader for classes.
 */
function get_class_loc(string $className): void {
	$className = str_replace('\\', DIRECTORY_SEPARATOR, $className);
	$classFile = LIB . 'Default/' . $className . '.php';
	if (is_file($classFile)) {
		require($classFile);
	}
}
