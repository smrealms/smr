<?php declare(strict_types=1);

namespace Smr\Pages\Account;

use Smr\Account;
use Smr\Page\AccountPage;
use Smr\Template;

class ErrorDisplay extends AccountPage {

	public function __construct(
		public readonly string $message,
	) {}

	public function build(Account $account, Template $template): void {
		$template->pageTopic = 'Error';
		$template->pageRenderer = fn() => ErrorDisplayRenderer::render($this->message);
	}

}
