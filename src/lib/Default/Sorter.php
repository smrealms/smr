<?php declare(strict_types=1);
class Sorter {

	private static string $sortKey;
	private static bool $reverseOrder;

	private static function cmpStrProp(mixed $a, mixed $b): int {
		return (self::$reverseOrder ? -1 : 1) * strcasecmp($a->{self::$sortKey}, $b->{self::$sortKey});
	}

	private static function cmpNumElement(mixed $a, mixed $b): int {
		return self::cmpNum($a[self::$sortKey], $b[self::$sortKey]);
	}

	private static function cmpNumProp(mixed $a, mixed $b): int {
		return self::cmpNum($a->{self::$sortKey}, $b->{self::$sortKey});
	}

	private static function cmpNumMethod(mixed $a, mixed $b): int {
		return self::cmpNum($a->{self::$sortKey}(), $b->{self::$sortKey}());
	}

	public static function cmpNum(mixed $a, mixed $b): int {
		if ($a == $b) {
			return 0;
		}
		return (self::$reverseOrder ? -1 : 1) * ($a < $b ? -1 : 1);
	}

	public static function sortByStrProp(array &$array, string $property, bool $reverseOrder = false): void {
		self::$sortKey = $property;
		self::$reverseOrder = $reverseOrder;
		usort($array, [self::class, 'cmpStrProp']);
	}

	public static function sortByNumElement(array &$array, string $property, bool $reverseOrder = false): void {
		self::$sortKey = $property;
		self::$reverseOrder = $reverseOrder;
		uasort($array, [self::class, 'cmpNumElement']);
	}

	public static function sortByNumProp(array &$array, string $property, bool $reverseOrder = false): void {
		self::$sortKey = $property;
		self::$reverseOrder = $reverseOrder;
		uasort($array, [self::class, 'cmpNumProp']);
	}

	public static function sortByNumMethod(array &$array, string $method, bool $reverseOrder = false): void {
		self::$sortKey = $method;
		self::$reverseOrder = $reverseOrder;
		uasort($array, [self::class, 'cmpNumMethod']);
	}

}
