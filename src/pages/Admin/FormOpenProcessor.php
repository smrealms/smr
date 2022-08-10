<?php declare(strict_types=1);

use Smr\Database;

		$var = Smr\Session::getInstance()->getCurrentVar();

		$db = Database::getInstance();
		$db->write('UPDATE open_forms SET open = ' . $db->escapeBoolean(!$var['is_open']) . ' WHERE type=' . $db->escapeString($var['type']));

		Page::create('admin/form_open.php')->go();
