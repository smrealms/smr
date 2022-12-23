<?php declare(strict_types=1);

namespace Smr\Page;

use Smr\Template;
use SmrAccount;

abstract class AccountPage extends Page {

	abstract public function build(SmrAccount $account, Template $template): void;

}
