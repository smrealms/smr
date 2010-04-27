<?php
class Sorter
{
		private static $sortKey;

		private static function cmpStrProp($a, $b)
		{
				return strcasecmp($a->{self::$sortKey}, $b->{self::$sortKey});
		}

		public static function sortByStrProp(array &$array, $property)
		{
				self::$sortKey = $property;
				usort($array, array(__CLASS__, 'cmpStrProp'));
		}

		private static function cmpNumProp($a, $b)
		{
			return self::cmpNum($a->{self::$sortKey}, $b->{self::$sortKey});
		}

		private static function cmpNumMethod($a, $b)
		{
			return self::cmpNum($a->{self::$sortKey}(), $b->{self::$sortKey}());
		}

		public static function cmpNum($a, $b)
		{
			if($a == $b) return 0;
			return $a < $b ? -1 : 1;
		}

		public static function sortByNumProp(array &$array, $property)
		{
				self::$sortKey = $property;
				usort($array, array(__CLASS__, 'cmpNumProp'));
		}

		public static function sortByNumMethod(array &$array, $method)
		{
				self::$sortKey = $method;
				usort($array, array(__CLASS__, 'cmpNumMethod'));
		}
}
?>