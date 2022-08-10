<?php declare(strict_types=1);

namespace Smr\Pages\Account;

use Smr\Page\AccountPageProcessor;
use Smr\Page\ReusableTrait;
use Smr\SectorLock;
use Smr\Session;
use SmrAccount;

class LogoffProcessor extends AccountPageProcessor {

	use ReusableTrait;

	public function build(SmrAccount $account): never {
		$account->log(LOG_TYPE_LOGIN, 'logged off from ' . getIpAddress());

		// Remove the lock if we're holding one (ie logged off from game screen)
		SectorLock::getInstance()->release();
		Session::getInstance()->destroy();

		// Send the player back to the login screen
		$msg = 'You have successfully logged off!';
		header('Location: /login.php?msg=' . rawurlencode(htmlspecialchars($msg, ENT_QUOTES)));
		exit;
	}

}
