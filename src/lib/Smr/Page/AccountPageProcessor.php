<?php declare(strict_types=1);

namespace Smr\Page;

use SmrAccount;

abstract class AccountPageProcessor extends Page {

	abstract public function build(SmrAccount $account): never;

}
