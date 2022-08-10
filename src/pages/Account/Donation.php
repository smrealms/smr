<?php declare(strict_types=1);

use Smr\Database;
use Smr\Epoch;

		$template = Smr\Template::getInstance();

		$template->assign('PageTopic', 'Donations');
		$db = Database::getInstance();
		$dbResult = $db->read('SELECT IFNULL(SUM(amount), 0) as total_donation FROM account_donated WHERE time > ' . $db->escapeNumber(Epoch::time()) . ' - (86400 * 90)');
		$template->assign('TotalDonation', $dbResult->record()->getInt('total_donation'));
