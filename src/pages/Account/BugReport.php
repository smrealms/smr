<?php declare(strict_types=1);

namespace Smr\Pages\Account;

use Smr\Page\AccountPage;
use Smr\Page\ReusableTrait;
use Smr\Template;
use SmrAccount;

class BugReport extends AccountPage {

	use ReusableTrait;

	public string $file = 'bug_report.php';

	public function build(SmrAccount $account, Template $template): void {
		$template->assign('PageTopic', 'Report a Bug');
	}

}
