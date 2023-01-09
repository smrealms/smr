<?php declare(strict_types=1);

namespace Smr\Pages\Admin;

use Exception;
use Smr\Account;
use Smr\Database;
use Smr\Page\AccountPageProcessor;
use Smr\Request;

class ServerStatusProcessor extends AccountPageProcessor {

	public function build(Account $account): never {
		$db = Database::getInstance();

		$action = Request::get('action');
		if ($action == 'Close') {
			$reason = Request::get('close_reason');
			$db->replace('game_disable', [
				'reason' => $db->escapeString($reason),
			]);
			$db->write('DELETE FROM active_session;');
			$msg = '<span class="green">SUCCESS: </span>You have closed the server. You will now be logged out!';
		} elseif ($action == 'Open') {
			$db->write('DELETE FROM game_disable;');
			$msg = '<span class="green">SUCCESS: </span>You have opened the server.';
		} else {
			throw new Exception('Unknown action: ' . $action);
		}

		$container = new AdminTools($msg);
		$container->go();
	}

}
