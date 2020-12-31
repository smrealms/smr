<?php declare(strict_types=1);
class Sorter {
	private static $sortKey;
	private static $reverseOrder;

	private static function cmpStrProp($a, $b) {
		return (self::$reverseOrder ? -1 : 1) * strcasecmp($a->{self::$sortKey}, $b->{self::$sortKey});
	}

	private static function cmpNumElement($a, $b) {
		return self::cmpNum($a[self::$sortKey], $b[self::$sortKey]);
	}

	private static function cmpNumProp($a, $b) {
		return self::cmpNum($a->{self::$sortKey}, $b->{self::$sortKey});
	}

	private static function cmpNumMethod($a, $b) {
		return self::cmpNum($a->{self::$sortKey}(), $b->{self::$sortKey}());
	}

	public static function cmpNum($a, $b) {
		if ($a == $b) return 0;
		return (self::$reverseOrder ? -1 : 1) * ($a < $b ? -1 : 1);
	}

	public static function sortByStrProp(array &$array, $property, $reverseOrder = false) {
		self::$sortKey = $property;
		self::$reverseOrder = $reverseOrder;
		usort($array, array(__CLASS__, 'cmpStrProp'));
	}

	public static function sortByNumElement(array &$array, $property, $reverseOrder = false) {
		self::$sortKey = $property;
		self::$reverseOrder = $reverseOrder;
		uasort($array, array(__CLASS__, 'cmpNumElement'));
	}

	public static function sortByNumProp(array &$array, $property, $reverseOrder = false) {
		self::$sortKey = $property;
		self::$reverseOrder = $reverseOrder;
		uasort($array, array(__CLASS__, 'cmpNumProp'));
	}

	public static function sortByNumMethod(array &$array, $method, $reverseOrder = false) {
		self::$sortKey = $method;
		self::$reverseOrder = $reverseOrder;
		uasort($array, array(__CLASS__, 'cmpNumMethod'));
	}
}
