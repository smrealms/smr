<?php declare(strict_types=1);

namespace Smr\Pages\Admin;

use Smr\Database;
use Smr\Page\AccountPage;
use Smr\Page\ReusableTrait;
use Smr\Template;
use SmrAccount;

class AlbumModerate extends AccountPage {

	use ReusableTrait;

	public string $file = 'admin/album_moderate.php';

	public function __construct(
		private readonly int $albumAccountID
	) {}

	public function build(SmrAccount $account, Template $template): void {
		$template->assign('PageTopic', 'Moderate Photo Album');

		require_once(LIB . 'Album/album_functions.php');

		$account_id = $this->albumAccountID;

		// check if the given account really has an entry
		$db = Database::getInstance();
		$dbResult = $db->read('SELECT * FROM album WHERE account_id = ' . $db->escapeNumber($account_id) . ' AND Approved = \'YES\'');
		$dbRecord = $dbResult->record();

		$disabled = $dbRecord->getBoolean('disabled');
		$location = $dbRecord->getNullableString('location');
		$email = $dbRecord->getNullableString('email');
		$website = $dbRecord->getNullableString('website');
		$day = $dbRecord->getInt('day');
		$month = $dbRecord->getInt('month');
		$year = $dbRecord->getInt('year');
		$other = nl2br($dbRecord->getString('other'));

		if (!empty($day) && !empty($month) && !empty($year)) {
			$birthdate = $month . ' / ' . $day . ' / ' . $year;
		}
		if (empty($birthdate) && !empty($year)) {
			$birthdate = 'Year ' . $year;
		}
		if (empty($birthdate)) {
			$birthdate = 'N/A';
		}

		$entry = [
			'disabled' => $disabled,
			'location' => empty($location) ? 'N/A' : $location,
			'email' => empty($email) ? 'N/A' : $email,
			'website' => empty($website) ? 'N/A' : '<a href="' . $website . '" target="_new">' . $website . '</a>',
			'birthdate' => $birthdate,
			'other' => empty($other) ? 'N/A' : $other,
			'nickname' => get_album_nick($account_id),
			'upload' => 'upload/' . $account_id,
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

		$default_email = 'Dear Photo Album User,' . EOL . EOL .
						 'You have received this email as notification that the picture you submitted to the Space Merchant Realms Photo Album has been temporarily disabled due to a Photo Album Rules violation.' . EOL .
						 'Please visit ' . URL . '/album.php or log into the SMR site to upload a new picture.' . EOL .
						 'Reply to this email when you have uploaded a new picture so we may re-enable your pic.' . EOL .
						 'Note: Please allow up to 48 hours for changes to occur.' . EOL .
						 'Thanks,' . EOL . EOL .
						 'Admin Team';
		$template->assign('DisableEmail', $default_email);

		$dbResult = $db->read('SELECT *
					FROM album_has_comments
					WHERE album_id = ' . $db->escapeNumber($account_id));
		$comments = [];
		foreach ($dbResult->records() as $dbRecord) {
			$comments[] = [
				'id' => $dbRecord->getInt('comment_id'),
				'date' => date($account->getDateTimeFormat(), $dbRecord->getInt('time')),
				'postee' => get_album_nick($dbRecord->getInt('post_id')),
				'msg' => $dbRecord->getString('msg'),
			];
		}
		$template->assign('Comments', $comments);
	}

}