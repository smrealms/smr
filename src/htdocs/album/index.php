<?php declare(strict_types=1);

use Smr\Album;
use Smr\Database;
use Smr\Pages\Album\EntryRenderer;
use Smr\Pages\Album\MainRenderer;
use Smr\Pages\Album\SearchResultsRenderer;
use Smr\Pages\Album\SkeletonRenderer;
use Smr\Request;
use Smr\Session;
use Smr\Template;

try {
	require_once('../../bootstrap.php');

	$session = Session::getInstance();
	$db = Database::getInstance();

	$albums = Album::getAllApproved();

	$letters = [];
	foreach (Album::getAllApproved() as $hofName => $album) {
		$letters[] = strtoupper($hofName[0]);
	}
	$letters = array_unique($letters);
	sort($letters);

	$matches = [];
	if (Request::has('nick')) {
		$inputNick = urldecode(Request::get('nick'));
		$matches = Album::getByHofName($inputNick);
	} elseif (Request::has('search')) {
		$inputNick = '%' . urldecode(Request::get('search')) . '%';
		$matches = Album::getByHofName($inputNick);
	}

	if (count($matches) === 0) {
		// Sort entries by descending page views, then take top 5
		uasort($albums, fn(Album $a, Album $b) => $b->pageViews <=> $a->pageViews);
		$mostViewed = [];
		foreach (array_slice($albums, 0, 5, true) as $nick => $album) {
			$mostViewed[$nick] = $album->pageViews;
		}

		// Sort entries by descending creation date, then take top 5
		uasort($albums, fn(Album $a, Album $b) => $b->created <=> $a->created);
		$dateFormat = $session->hasAccount() ? $session->getAccount()->getDateTimeFormat() : DEFAULT_DATE_TIME_FORMAT;
		$newest = [];
		foreach (array_slice($albums, 0, 5, true) as $nick => $album) {
			$newest[$nick] = date($dateFormat, $album->created);
		}

		$body = fn() => MainRenderer::render(
			MostViewed: $mostViewed,
			Newest: $newest,
		);

	} elseif (count($matches) === 1) {

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
			$prevNick = $dbResult->record()->getString('hof_name');
		} else {
			$prevNick = null;
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
			$nextNick = $dbResult->record()->getString('hof_name');
		} else {
			$nextNick = null;
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

		$dateFormat = $session->hasAccount() ? $session->getAccount()->getDateTimeFormat() : DEFAULT_DATE_TIME_FORMAT;
		$comments = $album->getComments($dateFormat);

		if ($session->hasAccount()) {
			$viewerDisplayName = $session->getAccount()->getHofDisplayName();
			$canModerate = $session->getAccount()->hasPermission(PERMISSION_MODERATE_PHOTO_ALBUM);
		} else {
			$viewerDisplayName = null;
			$canModerate = false;
		}

		$body = fn() => EntryRenderer::render(
			PrevNick: $prevNick,
			NextNick: $nextNick,
			Entry: $entry,
			Comments: $comments,
			CanModerate: $canModerate,
			ViewerDisplayName: $viewerDisplayName,
		);

	} else {
		$nicks = array_keys($matches);
		$body = fn() => SearchResultsRenderer::render($nicks);
	}

	Template::getInstance()->display(
		fn() => SkeletonRenderer::render($body, $letters),
	);

} catch (Throwable $e) {
	handleException($e);
}
