<?php

if ($_REQUEST['action'] == 'Approve') {
	$approved = 'YES';
}
else {
	$approved = 'NO';
}

$db->query('UPDATE album
			SET approved = '.$db->escapeString($approved).'
			WHERE account_id = ' . $db->escapeNumber($var['album_id']));

forward(create_container('skeleton.php', 'album_approve.php'));

?>