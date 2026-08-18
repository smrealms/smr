<?php declare(strict_types=1);

namespace Smr\Pages\Admin;

use Smr\Account;
use Smr\Database;
use Smr\Page\AccountPage;
use Smr\Page\ReusableTrait;
use Smr\Template;

class WordFilter extends AccountPage {

	use ReusableTrait;

	public function __construct(
		private readonly ?string $message = null,
	) {}

	public function build(Account $account, Template $template): void {
		$template->pageTopic = 'Word Filter';

		$db = Database::getInstance();
		$dbResult = $db->select('word_filter');
		$filteredWords = [];
		foreach ($dbResult->records() as $dbRecord) {
			$filteredWords[] = $dbRecord->getRow();
		}
		$template->pageRenderer = fn() => WordFilterRenderer::render(
			DelHREF: new WordFilterDeleteProcessor()->href(),
			AddHREF: new WordFilterAddProcessor()->href(),
			FilteredWords: $filteredWords,
			Message: $this->message,
		);
	}

}
