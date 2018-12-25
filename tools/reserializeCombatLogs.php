<?php

require_once('../htdocs/config.inc');

$db = new SmrMySqlDatabase();
$db2 = new SmrMySqlDatabase();

//$db->query('SELECT * FROM combat_logs WHERE type=\'PLAYER\' ORDER BY OCTET_LENGTH(result) DESC LIMIT 1');
//if($db->nextRecord())
//{
//	$x = $db->getField('result');
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

$db->query('SELECT result,log_id FROM combat_logs');
while($db->nextRecord()) {
	$db2->query('UPDATE combat_logs SET result='.$db2->escapeBinary(gzcompress(serialize(unserialize(gzuncompress($db->getField('result')))))).' WHERE log_id='.$db->getField('log_id'));
}
