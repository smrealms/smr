<?php declare(strict_types=1);

namespace Smr\Pages\Admin;

use Smr\Account;
use Smr\Database;
use Smr\Epoch;
use Smr\Html\Submit;
use Smr\Page\AccountPageProcessor;
use Smr\Request;

class AnnouncementCreateProcessor extends AccountPageProcessor {

	private const string ACTION = 'action';

	public readonly Submit $actionPreview;
	public readonly Submit $actionCreate;

	public function __construct() {
		$this->actionPreview = new Submit(self::ACTION, 'preview');
		$this->actionCreate = new Submit(self::ACTION, 'create');
	}

	public function build(Account $account): never {
		$message = Request::get('message');
		if (Request::get(self::ACTION) === $this->actionPreview->value) {
			$container = new AnnouncementCreate($message);
			$container->go();
		}

		// put the msg into the database
		$db = Database::getInstance();
		$db->insert('announcement', [
			'time' => Epoch::time(),
			'admin_id' => $account->getAccountID(),
			'msg' => $message,
		]);

		new AdminTools()->go();
	}

}
