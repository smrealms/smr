<?php declare(strict_types=1);

namespace Smr\Pages\Account;

use Smr\Account;
use Smr\Page\AccountPage;
use Smr\Template;

class ErrorDisplay extends AccountPage {

	public string $file = 'error.php';

	public function __construct(
		public readonly string $message,
	) {}

	public function build(Account $account, Template $template): void {
		$template->assign('PageTopic', 'Error');
		$template->assign('Message', $this->message);
	}

}
