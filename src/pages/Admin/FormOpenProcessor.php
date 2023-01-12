<?php declare(strict_types=1);

namespace Smr\Pages\Admin;

use Smr\Account;
use Smr\Database;
use Smr\Page\AccountPageProcessor;

class FormOpenProcessor extends AccountPageProcessor {

	public function __construct(
		private readonly bool $isOpen,
		private readonly string $type
	) {}

	public function build(Account $account): never {
		$db = Database::getInstance();
		$db->write('UPDATE open_forms SET open = ' . $db->escapeBoolean(!$this->isOpen) . ' WHERE type=' . $db->escapeString($this->type));

		(new FormOpen())->go();
	}

}
