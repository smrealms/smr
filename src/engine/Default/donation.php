<?php declare(strict_types=1);

$template = Smr\Template::getInstance();

$template->assign('PageTopic', 'Donations');
$db = Smr\Database::getInstance();
$dbResult = $db->read('SELECT SUM(amount) as total_donation FROM account_donated WHERE time > ' . $db->escapeNumber(Smr\Epoch::time()) . ' - (86400 * 90)');
if ($dbResult->hasRecord()) {
	$template->assign('TotalDonation', $dbResult->record()->getInt('total_donation'));
}
