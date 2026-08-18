<?php declare(strict_types=1);

namespace Smr\Pages\Admin;

use Smr\Account;
use Smr\Album;
use Smr\Page\AccountPage;
use Smr\Template;

class AlbumModerateSelect extends AccountPage {

	public function build(Account $account, Template $template): void {
		$template->pageTopic = 'Moderate Photo Album';

		$moderateHREF = (new AlbumModerateSelectProcessor())->href();

		// Get all accounts that are eligible for moderation
		$approved = [];
		foreach (Album::getAllApproved() as $nick => $album) {
			$approved[$album->accountID] = htmlentities($nick);
		}

		$template->pageRenderer = fn() => AlbumModerateSelectRenderer::render(
			ModerateHREF: $moderateHREF,
			Approved: $approved,
		);
	}

}
