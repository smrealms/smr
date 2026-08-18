<?php declare(strict_types=1);

namespace Smr\Pages\Admin;

use Smr\Account;
use Smr\Album;
use Smr\Epoch;
use Smr\Exceptions\AlbumNotFound;
use Smr\Page\AccountPage;
use Smr\Template;

class AlbumApprove extends AccountPage {

	public function build(Account $account, Template $template): void {
		$template->pageTopic = 'Approve Album Entries';

		try {
			$album = Album::getNextUnapproved();

			// get this user's nick
			$nick = Account::getAccount($album->accountID)->getHofDisplayName();

			// get the time that passed since the entry was last changed
			$time_passed = Epoch::time() - $album->lastChanged;

			$template->pageRenderer = fn() => AlbumApproveRenderer::render(
				Location: $album->getDisplayLocation(),
				Email: $album->getDisplayEmail(),
				Website: $album->getDisplayWebsite(),
				Other: $album->getDisplayOtherInfo(),
				ImgSrc: $album->getImageSrc(),
				Birthdate: $album->getDisplayBirthdate(),
				Nick: $nick,
				TimePassed: $time_passed,
				ApproveHREF: new AlbumApproveProcessor($album->accountID, approved: true)->href(),
				RejectHREF: new AlbumApproveProcessor($album->accountID, approved: false)->href(),
			);
		} catch (AlbumNotFound) {
			// No albums to approve
			$template->pageRenderer = fn() => AlbumApproveRenderer::renderEmpty();
		}

	}

}
