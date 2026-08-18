<?php declare(strict_types=1);

namespace Smr\Pages\Account;

use Exception;
use Smr\Account;
use Smr\Album;
use Smr\Exceptions\AlbumNotFound;
use Smr\Page\AccountPage;
use Smr\Page\ReusableTrait;
use Smr\Template;

class AlbumEdit extends AccountPage {

	use ReusableTrait;
	public function __construct(
		private readonly ?string $successMsg = null,
	) {}

	public function build(Account $account, Template $template): void {
		$template->pageTopic = 'Edit Photo';

		try {
			$album = new Album($account->getAccountID());
			$albumEntry = [
				'Location' => $album->location ?: '',
				'Email' => $album->email ?: '',
				'Website' => $album->website ?: '',
				'Day' => $album->birthDay ?: '',
				'Month' => $album->birthMonth ?: '',
				'Year' => $album->birthYear ?: '',
				'Other' => $album->otherInfo,
				'Status' => match ($album->approved) {
					'TBC' => '<span style="color:orange;">Waiting approval</span>',
					'NO' => '<span class="red">Approval denied</span>',
					'YES' => $album->isPictureDisabled ?
						'<span class="red">Picture Disabled</span>' :
						'<a href="album/?nick=' . urlencode($account->getHofName()) . '" class="dgreen">Approved</a>',
					default => throw new Exception('Unknown approved value'),
				},
			];
		} catch (AlbumNotFound) {
			$albumEntry = [
				'Location' => '',
				'Email' => '',
				'Website' => '',
				'Day' => '',
				'Month' => '',
				'Year' => '',
				'Other' => '',
				'Status' => '<span style="color:orange;">No entry</span>',
			];
		}

		if (is_readable(UPLOAD . $account->getAccountID())) {
			$albumEntry['Image'] = '/upload/' . $account->getAccountID();
		}

		$template->pageRenderer = fn() => AlbumEditRenderer::render(
			AlbumEntry: $albumEntry,
			AlbumEditHref: new AlbumEditProcessor()->href(),
			AlbumDeleteHref: new AlbumDeleteConfirm()->href(),
			SuccessMsg: $this->successMsg,
			ThisAccount: $account,
		);
	}

}
