<?php declare(strict_types=1);

namespace Smr\Pages\Account;

use Smr\Account;
use Smr\Page\AccountPage;
use Smr\Page\ReusableTrait;
use Smr\Template;

class BugReport extends AccountPage {

	use ReusableTrait;

	public string $file = 'bug_report.php';

	public function build(Account $account, Template $template): void {
		$template->assign('PageTopic', 'Report a Bug');
	}

}
