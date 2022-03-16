<?php declare(strict_types=1);

$template = Smr\Template::getInstance();
$session = Smr\Session::getInstance();
$player = $session->getPlayer();

$template->assign('PageTopic', 'View Forces');

$db = Smr\Database::getInstance();
$dbResult = $db->read('SELECT *
			FROM sector_has_forces
			WHERE owner_id = ' . $db->escapeNumber($player->getAccountID()) . '
			AND game_id = ' . $db->escapeNumber($player->getGameID()) . '
			AND expire_time >= ' . $db->escapeNumber(Smr\Epoch::time()) . '
			ORDER BY sector_id ASC');

$forces = [];
foreach ($dbResult->records() as $dbRecord) {
	$forces[] = SmrForce::getForce($player->getGameID(), $dbRecord->getInt('sector_id'), $dbRecord->getInt('owner_id'), false, $dbRecord);
}
$template->assign('Forces', $forces);
