<?php

$template->assign('PageTopic', 'View Forces');

$db->query('SELECT *
			FROM sector_has_forces
			WHERE owner_id = ' . $db->escapeNumber($player->getAccountID()) . '
			AND game_id = ' . $db->escapeNumber($player->getGameID()) . '
			AND expire_time >= '.$db->escapeNumber(TIME) . '
			ORDER BY sector_id ASC');

$forces = array();
while ($db->nextRecord()) {
	$forces[] = SmrForce::getForce($player->getGameID(), $db->getField('sector_id'), $db->getField('owner_id'), false, $db);
}
$template->assign('Forces', $forces);
