<?php declare(strict_types=1);

namespace Smr\Pages\Admin;

use Smr\Account;
use Smr\Database;
use Smr\Page\AccountPageProcessor;
use Smr\Request;

class WordFilterDeleteProcessor extends AccountPageProcessor {

	public function build(Account $account): never {
		if (Request::has('word_ids')) {
			$db = Database::getInstance();
			$db->write('DELETE FROM word_filter WHERE word_id IN (:word_ids)', [
				'word_ids' => $db->escapeArray(Request::getIntArray('word_ids')),
			]);
		}

		$container = new WordFilter();
		$container->go();
	}

}
