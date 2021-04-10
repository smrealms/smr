<?php declare(strict_types=1);
if (!Globals::isFeatureRequestOpen()) {
	create_error('Feature requests are currently not being accepted.');
}

$template = Smr\Template::getInstance();
$session = Smr\Session::getInstance();
$account = $session->getAccount();

$template->assign('PageTopic', 'Feature Request Comments');

$container = Page::copy($var);
$container['body'] = 'feature_request.php';
$template->assign('BackHref', $container->href());

$db = Smr\Database::getInstance();
$db->query('SELECT *
			FROM feature_request
			JOIN feature_request_comments USING(feature_request_id)
			WHERE feature_request_id = ' . $db->escapeNumber($var['RequestID']) . '
			ORDER BY comment_id ASC');
if ($db->getNumRows() > 0) {
	$featureModerator = $account->hasPermission(PERMISSION_MODERATE_FEATURE_REQUEST);
	$template->assign('FeatureModerator', $featureModerator);

	// variables needed to set the status for this feature request
	if ($featureModerator) {
		$template->assign('FeatureRequestId', $var['RequestID']);
		$template->assign('FeatureRequestStatusFormHREF', Page::create('feature_request_vote_processing.php')->href());
	}

	$featureRequestComments = array();
	while ($db->nextRecord()) {
		$commentID = $db->getInt('comment_id');
		$featureRequestComments[$commentID] = array(
								'CommentID' => $commentID,
								'Message' => $db->getField('text'),
								'Time' => date(DATE_FULL_SHORT, $db->getInt('posting_time')),
								'Anonymous' => $db->getBoolean('anonymous')
		);
		if ($featureModerator || !$db->getBoolean('anonymous')) {
			$featureRequestComments[$commentID]['PosterAccount'] = SmrAccount::getAccount($db->getInt('poster_id'));
		}
	}
	$template->assign('Comments', $featureRequestComments);
}

$container = Page::copy($var);
$container['url'] = 'feature_request_comment_processing.php';
unset($container['body']);
$template->assign('FeatureRequestCommentFormHREF', $container->href());
