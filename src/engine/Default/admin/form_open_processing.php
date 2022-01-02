<?php declare(strict_types=1);

$var = Smr\Session::getInstance()->getCurrentVar();

$db = Smr\Database::getInstance();
$db->write('UPDATE open_forms SET open = ' . $db->escapeBoolean(!$var['is_open']) . ' WHERE type=' . $db->escapeString($var['type']));

Page::create('skeleton.php', 'admin/form_open.php')->go();
