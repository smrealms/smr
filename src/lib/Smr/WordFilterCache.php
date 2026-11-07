<?php declare(strict_types=1);

namespace Smr;

use Smr\Container\DiContainer;

final class WordFilterCache {

	/** @var list<string> */
	private array $search = [];
	/** @var list<string> */
	private array $replace = [];

	public function __construct() {
		$db = Database::getInstance();
		$dbResult = $db->select('word_filter', [], ['word_value', 'word_replacement']);
		foreach ($dbResult->records() as $dbRecord) {
			$this->search[] = $dbRecord->getString('word_value');
			$this->replace[] = $dbRecord->getString('word_replacement');
		}
	}

	public static function getInstance(): self {
		return DiContainer::getClass(self::class);
	}

	public function filter(string $string): string {
		return str_ireplace($this->search, $this->replace, $string);
	}

}
