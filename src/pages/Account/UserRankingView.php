<?php declare(strict_types=1);

namespace Smr\Pages\Account;

use Smr\Account;
use Smr\Page\AccountPage;
use Smr\Page\ReusableTrait;
use Smr\Template;

class UserRankingView extends AccountPage {

	use ReusableTrait;

	public string $file = 'rankings_view.php';

	public function build(Account $account, Template $template): void {
		$template->assign('PageTopic', 'Extended User Rankings');
	}

}
