<?php declare(strict_types=1);

$template->assign('PageTopic', 'Donations');
$db->query('SELECT SUM(amount) as total_donation FROM account_donated WHERE time > ' . $db->escapeNumber(TIME) . ' - (86400 * 90)');
if ($db->nextRecord()) {
	$template->assign('TotalDonation', $db->getInt('total_donation'));
}
