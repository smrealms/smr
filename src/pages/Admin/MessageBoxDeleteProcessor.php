<?php declare(strict_types=1);

namespace Smr\Pages\Admin;

use Smr\Account;
use Smr\Database;
use Smr\Page\AccountPageProcessor;
use Smr\Request;

class MessageBoxDeleteProcessor extends AccountPageProcessor {

	public function __construct(
		private readonly int $boxTypeID
	) {}

	public function build(Account $account): never {
		$db = Database::getInstance();

		$action = Request::get('action');
		if ($action == 'Marked Messages') {
			if (!Request::has('message_id')) {
				create_error('You must choose the messages you want to delete.');
			}

			foreach (Request::getIntArray('message_id') as $id) {
				$db->write('DELETE FROM message_boxes WHERE message_id = ' . $db->escapeNumber($id));
			}
		} elseif ($action == 'All Messages') {
			$db->write('DELETE FROM message_boxes WHERE box_type_id = ' . $db->escapeNumber($this->boxTypeID));
		}

		(new MessageBoxView())->go();
	}

}
