<?php declare(strict_types=1);

namespace Smr\Page;

use Smr\Account;

abstract class AccountPageProcessor extends Page {

	abstract public function build(Account $account): never;

}
