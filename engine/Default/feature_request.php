<?php
if (!Globals::isFeatureRequestOpen()) {
	create_error('Feature requests are currently not being accepted.');
}

if (!isset($var['category'])) {
	SmrSession::updateVar('category', 'New');
}
$thisStatus = statusFromCategory($var['category']);

$template->assign('PageTopic', 'Feature Requests - ' . $var['category']);

// Feature Requests show up as new for this many days
const NEW_REQUEST_DAYS = 30;

$requestCategories = array(
	'New' => 'Open requests active within the past ' . NEW_REQUEST_DAYS . ' days',
	'All Open' => 'All requests that remain open for voting',
	'Accepted' => 'Features planned for future implementation',
	'Implemented' => 'Features that have already been implemented',
	'Rejected' => 'Features that are not planned for implementation',
);

$categoryTable = array();
foreach ($requestCategories as $category => $description) {
	$status = statusFromCategory($category);

	$container = $var;
	$container['category'] = $category;
	$categoryTable[$category] = array(
		'Selected' => $category == $var['category'],
		'HREF' => SmrSession::getNewHREF($container),
		'Count' => getFeaturesCount($status, ($category == 'New') ? NEW_REQUEST_DAYS : false),
		'Description' => $description
	);
}
$template->assign('CategoryTable', $categoryTable);

// Can the players vote for features on the current page?
$canVote = $thisStatus == 'Opened';
$template->assign('CanVote', $canVote);

if ($canVote) {
	$featureVotes = array();
	$db->query('SELECT * FROM account_votes_for_feature WHERE account_id = '.$account->getAccountID());
	while($db->nextRecord())
		$featureVotes[$db->getInt('feature_request_id')] = $db->getField('vote_type');
}
$db->query('SELECT * ' .
			'FROM feature_request ' .
			'JOIN feature_request_comments super USING(feature_request_id) ' .
			'WHERE comment_id = 1 ' .
			'AND status = ' . $db->escapeString($thisStatus) .
			($var['category'] == 'New' ? ' AND EXISTS(SELECT posting_time FROM feature_request_comments WHERE feature_request_id = super.feature_request_id AND posting_time > ' . (TIME - NEW_REQUEST_DAYS*86400) .')':'') .
			' ORDER BY (SELECT MAX(posting_time) FROM feature_request_comments WHERE feature_request_id = super.feature_request_id) DESC');
if ($db->getNumRows() > 0) {
	$featureModerator = $account->hasPermission(PERMISSION_MODERATE_FEATURE_REQUEST);
	$template->assign('FeatureModerator',$featureModerator);
	$template->assign('FeatureRequestVoteFormHREF',SmrSession::getNewHREF(create_container('feature_request_vote_processing.php', '')));

	$commentsContainer = $var;
	$commentsContainer['body'] = 'feature_request_comments.php';
	$db2 = new SmrMySqlDatabase();
	$featureRequests = array();
	while ($db->nextRecord()) {
		$featureRequestID = $db->getInt('feature_request_id');
		$featureRequests[$featureRequestID] = array(
								'RequestID' => $featureRequestID,
								'Message' => $db->getField('text'),
								'Votes' => array('FAVOURITE'=>$db->getInt('fav'),'YES'=>$db->getInt('yes'),'NO'=>$db->getInt('no')),
								'VotedFor' => isset($featureVotes[$featureRequestID]) ? $featureVotes[$featureRequestID] : false
		);
		if($featureModerator)
			$featureRequests[$featureRequestID]['RequestAccount'] =& SmrAccount::getAccount($db->getInt('poster_id'));

		if ($canVote) {
			$db2->query('SELECT COUNT(*), vote_type
						FROM account_votes_for_feature
						WHERE feature_request_id=' . $db2->escapeNumber($featureRequestID) . '
						GROUP BY vote_type');
			while($db2->nextRecord()) {
				$featureRequests[$featureRequestID]['Votes'][$db2->getField('vote_type')] = $db2->getInt('COUNT(*)');
			}
		}
		$db2->query('SELECT COUNT(*)
					FROM feature_request_comments
					WHERE feature_request_id=' . $db2->escapeNumber($featureRequestID));
		while($db2->nextRecord()) {
			$featureRequests[$featureRequestID]['Comments'] = $db2->getInt('COUNT(*)');
		}
		$commentsContainer['RequestID'] = $featureRequestID;
		$featureRequests[$featureRequestID]['CommentsHREF'] = SmrSession::getNewHREF($commentsContainer);
	}
	$template->assign('FeatureRequests',$featureRequests);
}

$template->assign('FeatureRequestFormHREF',SmrSession::getNewHREF(create_container('feature_request_processing.php', '')));

function statusFromCategory($category) {
	return ($category == 'New' || $category == 'All Open') ? 'Opened' : $category;
}

function getFeaturesCount($status, $daysNew = false) {
	global $db;
	$db->query('
		SELECT COUNT(*) AS count
		FROM feature_request
		JOIN feature_request_comments super USING(feature_request_id)
		WHERE comment_id = 1
		AND status = ' . $db->escapeString($status) .
		($daysNew ? ' AND EXISTS(SELECT posting_time FROM feature_request_comments WHERE feature_request_id = super.feature_request_id AND posting_time > ' . (TIME - $daysNew*86400) .')':'')
	);
	$db->nextRecord();
	return $db->getInt('count');
}
