<?php declare(strict_types=1);

namespace Smr\Pages\Admin;

use Smr\Account;
use Smr\Album;
use Smr\Page\AccountPage;
use Smr\Page\ReusableTrait;
use Smr\Template;

class AlbumModerate extends AccountPage {

	use ReusableTrait;

	public string $file = 'admin/album_moderate.php';

	public function __construct(
		private readonly int $albumAccountID,
	) {}

	public function build(Account $account, Template $template): void {
		$template->assign('PageTopic', 'Moderate Photo Album');

		$account_id = $this->albumAccountID;

		// check if the given account really has an entry
		$album = new Album($account_id);

		$entry = [
			'disabled' => $album->isPictureDisabled,
			'location' => $album->getDisplayLocation(),
			'email' => $album->getDisplayEmail(),
			'website' => $album->getDisplayWebsite(),
			'birthdate' => $album->getDisplayBirthdate(),
			'other' => $album->getDisplayOtherInfo(),
			'nickname' => Account::getAccount($account_id)->getHofDisplayName(),
			'upload' => $album->getImageSrc(),
		];
		$template->assign('Entry', $entry);

		$template->assign('BackHREF', (new AlbumModerateSelect())->href());

		$container = new AlbumModerateProcessor($account_id, 'reset_image');
		$template->assign('ResetImageHREF', $container->href());
		$container = new AlbumModerateProcessor($account_id, 'reset_location');
		$template->assign('ResetLocationHREF', $container->href());
		$container = new AlbumModerateProcessor($account_id, 'reset_email');
		$template->assign('ResetEmailHREF', $container->href());
		$container = new AlbumModerateProcessor($account_id, 'reset_website');
		$template->assign('ResetWebsiteHREF', $container->href());
		$container = new AlbumModerateProcessor($account_id, 'reset_birthdate');
		$template->assign('ResetBirthdateHREF', $container->href());
		$container = new AlbumModerateProcessor($account_id, 'reset_other');
		$template->assign('ResetOtherHREF', $container->href());
		$container = new AlbumModerateProcessor($account_id, 'delete_comment');
		$template->assign('DeleteCommentHREF', $container->href());

		$default_email = 'Dear Photo Album User,' . EOL . EOL
			. 'You have received this email as notification that the picture you submitted to the Space Merchant Realms Photo Album has been temporarily disabled due to a Photo Album Rules violation.' . EOL
			. 'Please visit ' . URL . '/album.php or log into the SMR site to upload a new picture.' . EOL
			. 'Reply to this email when you have uploaded a new picture so we may re-enable your pic.' . EOL
			. 'Note: Please allow up to 48 hours for changes to occur.' . EOL
			. 'Thanks,' . EOL . EOL
			. 'Admin Team';
		$template->assign('DisableEmail', $default_email);

		$template->assign('Comments', $album->getComments($account->getDateTimeFormat()));
	}

}
