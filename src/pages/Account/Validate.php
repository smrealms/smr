<?php declare(strict_types=1);

namespace Smr\Pages\Account;

use Smr\Account;
use Smr\Page\AccountPage;
use Smr\Template;

class Validate extends AccountPage {

	public string $file = 'validate.php';

	public function __construct(
		private readonly ?string $message = null,
	) {}

	public function build(Account $account, Template $template): void {
		$template->assign('Message', $this->message);
		$template->assign('PageTopic', 'Validation Reminder');
		$template->assign('ValidateFormHref', (new ValidateProcessor())->href());
	}

}
