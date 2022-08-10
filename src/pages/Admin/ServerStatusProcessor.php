<?php declare(strict_types=1);

use Smr\Database;
use Smr\Request;

		$db = Database::getInstance();

		$container = Page::create('admin/admin_tools.php');

		$action = Request::get('action');
		if ($action == 'Close') {
			$reason = Request::get('close_reason');
			$db->replace('game_disable', [
				'reason' => $db->escapeString($reason),
			]);
			$db->write('DELETE FROM active_session;');
			$container['msg'] = '<span class="green">SUCCESS: </span>You have closed the server. You will now be logged out!';
		} elseif ($action == 'Open') {
			$db->write('DELETE FROM game_disable;');
			$container['msg'] = '<span class="green">SUCCESS: </span>You have opened the server.';
		}

		$container->go();
