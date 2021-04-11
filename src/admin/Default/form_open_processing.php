<?php declare(strict_types=1);

$var = Smr\Session::getInstance()->getCurrentVar();

$db = Smr\Database::getInstance();
$db->query('UPDATE open_forms SET open = ' . $db->escapeBoolean(!$var['is_open']) . ' WHERE type=' . $db->escapeString($var['type']));

Page::create('skeleton.php', 'form_open.php')->go();
