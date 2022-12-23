<?php declare(strict_types=1);

namespace Smr\Pages\Admin;

use Smr\Database;
use Smr\Page\AccountPageProcessor;
use Smr\Request;
use SmrAccount;

class WordFilterDeleteProcessor extends AccountPageProcessor {

	public function build(SmrAccount $account): never {
		if (Request::has('word_ids')) {
			$db = Database::getInstance();
			$db->write('DELETE FROM word_filter WHERE word_id IN (' . $db->escapeArray(Request::getIntArray('word_ids')) . ')');
		}

		$container = new WordFilter();
		$container->go();
	}

}
