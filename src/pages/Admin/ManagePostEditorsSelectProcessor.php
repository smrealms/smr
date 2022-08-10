<?php declare(strict_types=1);

namespace Smr\Pages\Admin;

use Smr\Page\AccountPageProcessor;
use Smr\Request;
use SmrAccount;

class ManagePostEditorsSelectProcessor extends AccountPageProcessor {

	public function build(SmrAccount $account): never {
		$selectedGameID = Request::getInt('selected_game_id');
		(new ManagePostEditors($selectedGameID))->go();
	}

}
