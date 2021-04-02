<?php declare(strict_types=1);

$approved = $var['approved'] ? 'YES' : 'NO';

$db->query('UPDATE album
			SET approved = '.$db->escapeString($approved) . '
			WHERE account_id = ' . $db->escapeNumber($var['album_id']));

Page::create('skeleton.php', 'album_approve.php')->go();
