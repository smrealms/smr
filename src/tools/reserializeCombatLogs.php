<?php declare(strict_types=1);

use Smr\Database;

require_once('../bootstrap.php');

$db = Database::getInstance();

$dbResult = $db->read('SELECT result,log_id FROM combat_logs');
foreach ($dbResult->records() as $dbRecord) {
	$db->update(
		'combat_logs',
		['result' => $db->escapeObject($dbRecord->getObject('result', true), true)],
		['log_id' => $dbRecord->getInt('log_id')],
	);
}
