<?php declare(strict_types=1);

use Smr\Database;
use Smr\Request;

		if (!Request::has('notify_id')) {
			create_error('You must choose the messages you want to delete.');
		}

		$db = Database::getInstance();
		$db->write('DELETE FROM message_notify WHERE notify_id IN (' . $db->escapeArray(Request::getIntArray('notify_id')) . ')');

		Page::create('admin/notify_view.php')->go();
