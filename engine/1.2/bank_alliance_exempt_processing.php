<?php

//only if we are coming from the bank screen do we unexempt selection first
if (isset($var['minVal'])) {
	for ($i = $var['minVal']; $i <= $var['maxVal']; $i++) $temp[] = $i;
	$db->query("UPDATE alliance_bank_transactions SET exempt = 0 WHERE game_id = $player->game_id AND alliance_id = $player->alliance_id " .
				"AND transaction_id IN (" . implode(',',$temp) . ")");
	unset($temp);
}

if (isset($_REQUEST['exempt']) && is_array($_REQUEST['exempt'])) {
	foreach ($_REQUEST['exempt'] as $trans_id => $value) $temp[] = $trans_id;
	$db->query("UPDATE alliance_bank_transactions SET exempt = 1, request_exempt = 0 WHERE game_id = $player->game_id AND alliance_id = $player->alliance_id " .
				"AND transaction_id IN (" . implode(',',$temp) . ")");
}

$container = array();
$container["url"] = "skeleton.php";
if (isset($var['minVal']))
	$container["body"] = "bank_alliance.php";
else
	$container["body"] = "alliance_exempt_authorize.php";
forward($container);

?>