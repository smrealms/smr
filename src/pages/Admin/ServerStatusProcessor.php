<?php declare(strict_types=1);

namespace Smr\Pages\Admin;

use Exception;
use Smr\Account;
use Smr\Database;
use Smr\Html\Submit;
use Smr\Page\AccountPageProcessor;
use Smr\Request;

class ServerStatusProcessor extends AccountPageProcessor {

	private const string ACTION = 'action';

	public readonly Submit $actionClose;
	public readonly Submit $actionOpen;

	public function __construct() {
		$this->actionClose = new Submit(self::ACTION, 'Close');
		$this->actionOpen = new Submit(self::ACTION, 'Open');
	}

	public function build(Account $account): never {
		$db = Database::getInstance();

		$action = Request::get(self::ACTION);
		if ($action === $this->actionClose->value) {
			$reason = Request::get('close_reason');
			$db->replace('game_disable', [
				'reason' => $reason,
			]);
			$db->write('DELETE FROM active_session;');
			$msg = '<span class="green">SUCCESS: </span>You have closed the server. You will now be logged out!';
		} elseif ($action === $this->actionOpen->value) {
			$db->write('DELETE FROM game_disable;');
			$msg = '<span class="green">SUCCESS: </span>You have opened the server.';
		} else {
			throw new Exception('Unknown action: ' . $action);
		}

		$container = new AdminTools($msg);
		$container->go();
	}

}
