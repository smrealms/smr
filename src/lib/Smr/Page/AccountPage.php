<?php declare(strict_types=1);

namespace Smr\Page;

use Smr\Account;
use Smr\Template;

abstract class AccountPage extends Page {

	abstract public function build(Account $account, Template $template): void;

}
