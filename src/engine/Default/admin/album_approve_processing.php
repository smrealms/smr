<?php declare(strict_types=1);

$var = Smr\Session::getInstance()->getCurrentVar();

$approved = $var['approved'] ? 'YES' : 'NO';

$db = Smr\Database::getInstance();
$db->write('UPDATE album
			SET approved = ' . $db->escapeString($approved) . '
			WHERE account_id = ' . $db->escapeNumber($var['album_id']));

Page::create('admin/album_approve.php')->go();
