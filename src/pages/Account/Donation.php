<?php declare(strict_types=1);

namespace Smr\Pages\Account;

use Smr\Account;
use Smr\Database;
use Smr\Epoch;
use Smr\Page\AccountPage;
use Smr\Page\ReusableTrait;
use Smr\Template;

class Donation extends AccountPage {

	use ReusableTrait;

	public string $file = 'donation.php';

	public function build(Account $account, Template $template): void {
		$template->assign('PageTopic', 'Donations');
		$db = Database::getInstance();
		$dbResult = $db->read('SELECT IFNULL(SUM(amount), 0) as total_donation FROM account_donated WHERE time > :hide_donation_time', [
			'hide_donation_time' => $db->escapeNumber(Epoch::time() - (86400 * 90)), // 90 days
		]);
		$template->assign('TotalDonation', $dbResult->record()->getInt('total_donation'));
	}

}
