<?php declare(strict_types=1);

namespace Smr\Pages\Admin;

use Smr\Database;
use Smr\Page\AccountPage;
use Smr\Page\ReusableTrait;
use Smr\Template;
use SmrAccount;

class WordFilter extends AccountPage {

	use ReusableTrait;

	public string $file = 'admin/word_filter.php';

	public function __construct(
		private readonly ?string $message = null
	) {}

	public function build(SmrAccount $account, Template $template): void {
		$template->assign('PageTopic', 'Word Filter');

		$template->assign('Message', $this->message);

		$db = Database::getInstance();
		$dbResult = $db->read('SELECT * FROM word_filter');
		if ($dbResult->hasRecord()) {
			$container = new WordFilterDeleteProcessor();
			$template->assign('DelHREF', $container->href());

			$filteredWords = [];
			foreach ($dbResult->records() as $dbRecord) {
				$filteredWords[] = $dbRecord->getRow();
			}
			$template->assign('FilteredWords', $filteredWords);
		}

		$container = new WordFilterAddProcessor();
		$template->assign('AddHREF', $container->href());
	}

}
