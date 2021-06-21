<?php declare(strict_types=1);

require_once('../bootstrap.php');

$db = Smr\Database::getInstance();

//$dbResult = $db->read('SELECT * FROM combat_logs WHERE type=\'PLAYER\' ORDER BY OCTET_LENGTH(result) DESC LIMIT 1');
//if ($dbResult->hasRecord())
//{
//	$x = $dbResult->record()->getField('result');
//	$y = gzuncompress($x);
//	var_dump($y);
//
//	$z = serialize(unserialize($y));
//	var_dump($z);
//
//	var_dump(strlen($x));
//	var_dump(strlen(gzcompress($z)));
//	var_dump(strlen(bzcompress($z)));
//}

$dbResult = $db->read('SELECT result,log_id FROM combat_logs');
foreach ($dbResult->records() as $dbRecord) {
	$db->write('UPDATE combat_logs SET result=' . $db->escapeObject($dbRecord->getObject('result', true), true) . ' WHERE log_id=' . $dbRecord->getField('log_id'));
}
