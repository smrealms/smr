<?php declare(strict_types=1);

use Smr\Album;
use Smr\Database;
use Smr\Request;
use Smr\Session;
use Smr\Template;

try {
	require_once('../../bootstrap.php');

	$template = new Template();
	$session = Session::getInstance();
	$db = Database::getInstance();

	$albums = Album::getAllApproved();

	$letters = [];
	foreach (Album::getAllApproved() as $hofName => $album) {
		$letters[] = strtoupper($hofName[0]);
	}
	$letters = array_unique($letters);
	sort($letters);
	$template->assign('Letters', $letters);

	$matches = [];
	if (Request::has('nick')) {
		$inputNick = urldecode(Request::get('nick'));
		$matches = Album::getByHofName($inputNick);
	} elseif (Request::has('search')) {
		$inputNick = '%' . urldecode(Request::get('search')) . '%';
		$matches = Album::getByHofName($inputNick);
	}

	if (count($matches) === 0) {
		$template->assign('Body', 'album/main.php');

		// Sort entries by descending page views, then take top 5
		uasort($albums, fn(Album $a, Album $b) => $b->pageViews <=> $a->pageViews);
		$mostViewed = [];
		foreach (array_slice($albums, 0, 5, true) as $nick => $album) {
			$mostViewed[$nick] = $album->pageViews;
		}
		$template->assign('MostViewed', $mostViewed);

		// Sort entries by descending creation date, then take top 5
		uasort($albums, fn(Album $a, Album $b) => $b->created <=> $a->created);
		$dateFormat = $session->hasAccount() ? $session->getAccount()->getDateTimeFormat() : DEFAULT_DATE_TIME_FORMAT;
		$newest = [];
		foreach (array_slice($albums, 0, 5, true) as $nick => $album) {
			$newest[$nick] = date($dateFormat, $album->created);
		}
		$template->assign('Newest', $newest);

	} elseif (count($matches) === 1) {
		$template->assign('Body', 'album/entry.php');

		$nick = key($matches);
		$album = $matches[$nick];

		// Add a page view for this album entry
		if ($session->hasAccount() && $album->accountID !== $session->getAccountID()) {
			$db->write('UPDATE album
				SET page_views = page_views + 1
				WHERE account_id = :account_id AND
					approved = \'YES\'', [
				'account_id' => $db->escapeNumber($album->accountID),
			]);
		}

		// Get the previous entry
		$dbResult = $db->read('SELECT hof_name
				FROM album JOIN account USING(account_id)
				WHERE hof_name < :hof_name AND
					approved = \'YES\'
				ORDER BY hof_name DESC
				LIMIT 1', [
			'hof_name' => $db->escapeString($nick),
		]);
		if ($dbResult->hasRecord()) {
			$template->assign('PrevNick', $dbResult->record()->getString('hof_name'));
		}

		// Get the next entry
		$dbResult = $db->read('SELECT hof_name
				FROM album JOIN account USING(account_id)
				WHERE hof_name > :hof_name AND
					approved = \'YES\'
				ORDER BY hof_name
				LIMIT 1', [
			'hof_name' => $db->escapeString($nick),
		]);
		if ($dbResult->hasRecord()) {
			$template->assign('NextNick', $dbResult->record()->getString('hof_name'));
		}

		$entry = [
			'Nick' => $nick,
			'PageViews' => $album->pageViews,
			'ImgSrc' => $album->getImageSrc(),
			'Location' => $album->getDisplayLocation(),
			'Email' => $album->getDisplayEmail(),
			'Website' => $album->getDisplayWebsite(),
			'Birthdate' => $album->getDisplayBirthdate(),
			'OtherInfo' => $album->getDisplayOtherInfo(),
			'AccountID' => $album->accountID,
		];
		$template->assign('Entry', $entry);

		$dateFormat = $session->hasAccount() ? $session->getAccount()->getDateTimeFormat() : DEFAULT_DATE_TIME_FORMAT;
		$template->assign('Comments', $album->getComments($dateFormat));

		if ($session->hasAccount()) {
			$template->assign('ViewerDisplayName', $session->getAccount()->getHofDisplayName());
			$canModerate = $session->getAccount()->hasPermission(PERMISSION_MODERATE_PHOTO_ALBUM);
			$template->assign('CanModerate', $canModerate);
		}

	} else {
		$template->assign('Body', 'album/search_results.php');
		$template->assign('Nicks', array_keys($matches));
	}

	$template->display('album/skeleton.php');

} catch (Throwable $e) {
	handleException($e);
}
