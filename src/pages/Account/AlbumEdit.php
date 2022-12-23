<?php declare(strict_types=1);

namespace Smr\Pages\Account;

use Smr\Database;
use Smr\Page\AccountPage;
use Smr\Page\ReusableTrait;
use Smr\Template;
use SmrAccount;

class AlbumEdit extends AccountPage {

	use ReusableTrait;

	public string $file = 'album_edit.php';

	public function __construct(
		private readonly ?string $successMsg = null
	) {}

	public function build(SmrAccount $account, Template $template): void {
		$template->assign('PageTopic', 'Edit Photo');

		$db = Database::getInstance();
		$dbResult = $db->read('SELECT * FROM album WHERE account_id = ' . $db->escapeNumber($account->getAccountID()));
		if ($dbResult->hasRecord()) {
			$dbRecord = $dbResult->record();
			$day = $dbRecord->getInt('day');
			$month = $dbRecord->getInt('month');
			$year = $dbRecord->getInt('year');
			$albumEntry = [
				'Location' => $dbRecord->getNullableString('location'),
				'Email' => $dbRecord->getNullableString('email'),
				'Website' => $dbRecord->getNullableString('website'),
				'Day' => $day > 0 ? $day : '',
				'Month' => $month > 0 ? $month : '',
				'Year' => $year > 0 ? $year : '',
				'Other' => $dbRecord->getString('other'),
			];
			$approved = $dbRecord->getString('approved');

			if ($approved == 'TBC') {
				$albumEntry['Status'] = ('<span style="color:orange;">Waiting approval</span>');
			} elseif ($approved == 'NO') {
				$albumEntry['Status'] = ('<span class="red">Approval denied</span>');
			} elseif ($dbRecord->getBoolean('disabled')) {
				$albumEntry['Status'] = ('<span class="red">Disabled</span>');
			} elseif ($approved == 'YES') {
				$albumEntry['Status'] = ('<a href="album/?nick=' . urlencode($account->getHofName()) . '" class="dgreen">Online</a>');
			}

			if (is_readable(UPLOAD . $account->getAccountID())) {
				$albumEntry['Image'] = '/upload/' . $account->getAccountID();
			}

			$template->assign('AlbumEntry', $albumEntry);
		}

		$template->assign('AlbumEditHref', (new AlbumEditProcessor())->href());
		$template->assign('AlbumDeleteHref', (new AlbumDeleteConfirm())->href());

		$template->assign('SuccessMsg', $this->successMsg);
	}

}
