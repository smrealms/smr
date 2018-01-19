<?php
if (!Globals::isFeatureRequestOpen()) {
	create_error('Feature requests are currently not being accepted.');
}

$template->assign('PageTopic','Feature Request');

if(!isset($var['Status'])) {
	SmrSession::updateVar('Status', 'Opened');
}

$container = $var;
$container['Status'] = 'Accepted';
$template->assign('ViewAcceptedFeaturesHref', SmrSession::getNewHREF($container));

$container = $var;
$container['Status'] = 'Implemented';
$template->assign('ViewImplementedFeaturesHref',SmrSession::getNewHREF($container));

$container = $var;
$container['Status'] = 'Opened';
$container['ShowOld'] = true;
$template->assign('ShowOldFeaturesHref',SmrSession::getNewHREF($container));

$container = $var;
$container['Status'] = 'Rejected';
$template->assign('ShowRejectedFeaturesHref',SmrSession::getNewHREF($container));

$showCurrent = (!isset($var['ShowOld']) || $var['ShowOld']!==true) && $var['Status']=='Opened';
$template->assign('ShowCurrent',$showCurrent);
$template->assign('Status',$var['Status']);

if($var['Status'] != 'Accepted') {
	$template->assign('AcceptedTotal', getFeaturesCount('Accepted'));
}
if($var['Status'] != 'Implemented') {
	$template->assign('PreviousImplementedTotal', getFeaturesCount('Implemented'));
}
if($var['Status'] != 'Opened' || !$showCurrent) {
	$template->assign('CurrentTotal', getFeaturesCount('Opened', true));
}
if($var['Status'] != 'Opened' || $showCurrent) {
	$template->assign('OldTotal', getFeaturesCount('Opened'));
}
if($var['Status'] != 'Rejected') {
	$template->assign('RejectedTotal', getFeaturesCount('Rejected'));
}

if($var['Status'] == 'Opened') {
	$featureVotes = array();
	$db->query('SELECT * FROM account_votes_for_feature WHERE account_id = '.SmrSession::$account_id);
	while($db->nextRecord())
		$featureVotes[$db->getInt('feature_request_id')] = $db->getField('vote_type');
}
$db->query('SELECT * ' .
			'FROM feature_request ' .
			'JOIN feature_request_comments super USING(feature_request_id) ' .
			'WHERE comment_id = 1 ' .
			'AND status = ' . $db->escapeString($var['Status']) .
			($showCurrent?' AND EXISTS(SELECT posting_time FROM feature_request_comments WHERE feature_request_id = super.feature_request_id AND posting_time > ' . (TIME-14*86400) .')':'') .
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
		
		if($var['Status'] == 'Opened') {
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
	$template->assignByRef('FeatureRequests',$featureRequests);
}

$template->assign('FeatureRequestFormHREF',SmrSession::getNewHREF(create_container('feature_request_processing.php', '')));

function getFeaturesCount($status, $onlyCurrent = false) {
	global $db;
	$db->query('
		SELECT COUNT(*) AS count
		FROM feature_request
		JOIN feature_request_comments super USING(feature_request_id)
		WHERE comment_id = 1
		AND status = ' . $db->escapeString($status) .
		($onlyCurrent?' AND EXISTS(SELECT posting_time FROM feature_request_comments WHERE feature_request_id = super.feature_request_id AND posting_time > ' . (TIME-14*86400) .')':'')
	);
	$db->nextRecord();
	return $db->getInt('count');
}
?>
