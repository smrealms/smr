<?php declare(strict_types=1);

namespace Smr\Pages\Admin;

use Smr\Account;
use Smr\Database;
use Smr\Page\AccountPageProcessor;
use Smr\Request;

class WordFilterAddProcessor extends AccountPageProcessor {

	public function build(Account $account): never {
		$word = strtoupper(Request::get('Word'));
		$word_replacement = strtoupper(Request::get('WordReplacement'));

		$db = Database::getInstance();
		$dbResult = $db->read('SELECT 1 FROM word_filter WHERE word_value = :word_value LIMIT 1', [
			'word_value' => $db->escapeString($word),
		]);
		if ($dbResult->hasRecord()) {
			$msg = '<span class="red bold">ERROR: </span>This word is already filtered!';
		} else {
			$db->insert('word_filter', [
				'word_value' => $word,
				'word_replacement' => $word_replacement,
			]);
			$msg = '<span class="yellow">' . $word . '</span> will now be replaced with <span class="yellow">' . $word_replacement . '</span>.';
		}
		$container = new WordFilter($msg);
		$container->go();
	}

}
