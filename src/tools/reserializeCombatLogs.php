<?php declare(strict_types=1);

require_once('../bootstrap.php');

$db = Smr\Database::getInstance();

$dbResult = $db->read('SELECT result,log_id FROM combat_logs');
foreach ($dbResult->records() as $dbRecord) {
	$db->write('UPDATE combat_logs SET result=' . $db->escapeObject($dbRecord->getObject('result', true), true) . ' WHERE log_id=' . $dbRecord->getInt('log_id'));
}
