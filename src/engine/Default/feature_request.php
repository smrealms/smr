<?php declare(strict_types=1);

$template = Smr\Template::getInstance();
$db = Smr\Database::getInstance();
$session = Smr\Session::getInstance();
$var = $session->getCurrentVar();
$account = $session->getAccount();

if (!Globals::isFeatureRequestOpen()) {
	create_error('Feature requests are currently not being accepted.');
}

if (!isset($var['category'])) {
	$var['category'] = 'New';
}
$thisStatus = statusFromCategory($var['category']);

$template->assign('PageTopic', 'Feature Requests - ' . $var['category']);

// Feature Requests show up as new for this many days
const NEW_REQUEST_DAYS = 30;

$requestCategories = [
	'New' => 'Open requests active within the past ' . NEW_REQUEST_DAYS . ' days',
	'All Open' => 'All requests that remain open for voting',
	'Accepted' => 'Features planned for future implementation',
	'Implemented' => 'Features that have already been implemented',
	'Rejected' => 'Features that are not planned for implementation',
];

$categoryTable = [];
foreach ($requestCategories as $category => $description) {
	$status = statusFromCategory($category);

	$container = Page::copy($var);
	$container['category'] = $category;
	$categoryTable[$category] = [
		'Selected' => $category == $var['category'],
		'HREF' => $container->href(),
		'Count' => getFeaturesCount($status, ($category == 'New') ? NEW_REQUEST_DAYS : false),
		'Description' => $description,
	];
}
$template->assign('CategoryTable', $categoryTable);

// Can the players vote for features on the current page?
$canVote = $thisStatus == 'Opened';
$template->assign('CanVote', $canVote);

if ($canVote) {
	$featureVotes = [];
	$dbResult = $db->read('SELECT * FROM account_votes_for_feature WHERE account_id = ' . $account->getAccountID());
	foreach ($dbResult->records() as $dbRecord) {
		$featureVotes[$dbRecord->getInt('feature_request_id')] = $dbRecord->getField('vote_type');
	}
}
$dbResult = $db->read('SELECT * ' .
			'FROM feature_request ' .
			'JOIN feature_request_comments super USING(feature_request_id) ' .
			'WHERE comment_id = 1 ' .
			'AND status = ' . $db->escapeString($thisStatus) .
			($var['category'] == 'New' ? ' AND EXISTS(SELECT posting_time FROM feature_request_comments WHERE feature_request_id = super.feature_request_id AND posting_time > ' . (Smr\Epoch::time() - NEW_REQUEST_DAYS * 86400) . ')' : '') .
			' ORDER BY (SELECT MAX(posting_time) FROM feature_request_comments WHERE feature_request_id = super.feature_request_id) DESC');
if ($dbResult->hasRecord()) {
	$featureModerator = $account->hasPermission(PERMISSION_MODERATE_FEATURE_REQUEST);
	$template->assign('FeatureModerator', $featureModerator);
	$template->assign('FeatureRequestVoteFormHREF', Page::create('feature_request_vote_processing.php', '')->href());

	$commentsContainer = Page::copy($var);
	$commentsContainer['body'] = 'feature_request_comments.php';
	$featureRequests = [];
	foreach ($dbResult->records() as $dbRecord) {
		$featureRequestID = $dbRecord->getInt('feature_request_id');
		$featureRequests[$featureRequestID] = [
			'RequestID' => $featureRequestID,
			'Message' => $dbRecord->getField('text'),
			'Votes' => [
				'FAVOURITE' => $dbRecord->getInt('fav'),
				'YES' => $dbRecord->getInt('yes'),
				'NO' => $dbRecord->getInt('no'),
			],
			'VotedFor' => $featureVotes[$featureRequestID] ?? false,
		];
		if ($featureModerator) {
			$featureRequests[$featureRequestID]['RequestAccount'] = SmrAccount::getAccount($dbRecord->getInt('poster_id'));
		}

		if ($canVote) {
			$dbResult2 = $db->read('SELECT COUNT(*), vote_type
						FROM account_votes_for_feature
						WHERE feature_request_id=' . $db->escapeNumber($featureRequestID) . '
						GROUP BY vote_type');
			foreach ($dbResult2->records() as $dbRecord2) {
				$featureRequests[$featureRequestID]['Votes'][$dbRecord2->getField('vote_type')] = $dbRecord2->getInt('COUNT(*)');
			}
		}
		$dbResult2 = $db->read('SELECT COUNT(*)
					FROM feature_request_comments
					WHERE feature_request_id=' . $db->escapeNumber($featureRequestID));
		foreach ($dbResult2->records() as $dbRecord2) {
			$featureRequests[$featureRequestID]['Comments'] = $dbRecord2->getInt('COUNT(*)');
		}
		$commentsContainer['RequestID'] = $featureRequestID;
		$featureRequests[$featureRequestID]['CommentsHREF'] = $commentsContainer->href();
	}
	$template->assign('FeatureRequests', $featureRequests);
}

$template->assign('FeatureRequestFormHREF', Page::create('feature_request_processing.php', '')->href());

function statusFromCategory(string $category): string {
	return ($category == 'New' || $category == 'All Open') ? 'Opened' : $category;
}

function getFeaturesCount(string $status, int|false $daysNew = false): int {
	$db = Smr\Database::getInstance();
	$dbResult = $db->read('
		SELECT COUNT(*) AS count
		FROM feature_request
		JOIN feature_request_comments super USING(feature_request_id)
		WHERE comment_id = 1
		AND status = ' . $db->escapeString($status) .
		($daysNew ? ' AND EXISTS(SELECT posting_time FROM feature_request_comments WHERE feature_request_id = super.feature_request_id AND posting_time > ' . (Smr\Epoch::time() - $daysNew * 86400) . ')' : '')
	);
	return $dbResult->record()->getInt('count');
}
