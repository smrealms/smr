<?php declare(strict_types=1);

namespace Smr\Pages\Account;

use Smr\Account;
use Smr\Page\AccountPage;
use Smr\Page\ReusableTrait;
use Smr\Pages\Shared\UserRankingViewRenderer;
use Smr\Template;

class UserRankingView extends AccountPage {

	use ReusableTrait;
	public function build(Account $account, Template $template): void {
		$template->pageTopic = 'Extended User Rankings';

		$template->pageRenderer = fn() => UserRankingViewRenderer::render(
			ThisAccount: $account,
			ThisPlayer: null,
		);
	}

}
