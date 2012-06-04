<?php

$container = array();
$container['url'] = 'skeleton.php';
$container['body'] = 'message_blacklist.php';

if(!isset($_REQUEST['entry_ids']) || !is_array($_REQUEST['entry_ids'])) {
	$container['error'] = 4;	
	forward($container);
	exit;
}

foreach($_REQUEST['entry_ids'] as $entry_id) {
	if(!is_numeric($entry_id)) {
		$container['error'] = 5;	
		forward($container);
		exit;
	}
	else {
		$entry_ids[] = $entry_id;
	}
}

$db = new SMR_DB();
$db->query('DELETE FROM message_blacklist WHERE account_id=' . SmrSession::$account_id . ' AND entry_id IN (' . implode(',',$entry_ids) . ')');
forward($container);

?>
