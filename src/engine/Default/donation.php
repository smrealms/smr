<?php declare(strict_types=1);

$template = Smr\Template::getInstance();

$template->assign('PageTopic', 'Donations');
$db = Smr\Database::getInstance();
$db->query('SELECT SUM(amount) as total_donation FROM account_donated WHERE time > ' . $db->escapeNumber(Smr\Epoch::time()) . ' - (86400 * 90)');
if ($db->nextRecord()) {
	$template->assign('TotalDonation', $db->getInt('total_donation'));
}
