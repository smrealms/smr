<?php declare(strict_types=1);

namespace Smr\Pages\Admin;

use Smr\Account;
use Smr\Album;
use Smr\Epoch;
use Smr\Exceptions\AlbumNotFound;
use Smr\Page\AccountPage;
use Smr\Template;

class AlbumApprove extends AccountPage {

	public string $file = 'admin/album_approve.php';

	public function build(Account $account, Template $template): void {
		$template->assign('PageTopic', 'Approve Album Entries');

		try {
			$album = Album::getNextUnapproved();

			$template->assign('Location', $album->getDisplayLocation());
			$template->assign('Email', $album->getDisplayEmail());
			$template->assign('Website', $album->getDisplayWebsite());
			$template->assign('Other', $album->getDisplayOtherInfo());
			$template->assign('ImgSrc', $album->getImageSrc());
			$template->assign('Birthdate', $album->getDisplayBirthdate());

			// get this user's nick
			$nick = Account::getAccount($album->accountID)->getHofDisplayName();
			$template->assign('Nick', $nick);

			// get the time that passed since the entry was last changed
			$time_passed = Epoch::time() - $album->lastChanged;
			$template->assign('TimePassed', $time_passed);

			$container = new AlbumApproveProcessor($album->accountID, approved: true);
			$template->assign('ApproveHREF', $container->href());
			$container = new AlbumApproveProcessor($album->accountID, approved: false);
			$template->assign('RejectHREF', $container->href());
		} catch (AlbumNotFound) {
			// No albums to approve
		}
	}

}
