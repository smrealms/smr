<?php declare(strict_types=1);

use Smr\Database;

		if (!Globals::isFeatureRequestOpen()) {
			create_error('Feature requests are currently not being accepted.');
		}

		$template = Smr\Template::getInstance();
		$session = Smr\Session::getInstance();
		$var = $session->getCurrentVar();
		$account = $session->getAccount();

		$template->assign('PageTopic', 'Feature Request Comments');

		$container = Page::create('feature_request.php', $var);
		$template->assign('BackHref', $container->href());

		$db = Database::getInstance();
		$dbResult = $db->read('SELECT *
					FROM feature_request
					JOIN feature_request_comments USING(feature_request_id)
					WHERE feature_request_id = ' . $db->escapeNumber($var['RequestID']) . '
					ORDER BY comment_id ASC');
		if ($dbResult->hasRecord()) {
			$featureModerator = $account->hasPermission(PERMISSION_MODERATE_FEATURE_REQUEST);
			$template->assign('FeatureModerator', $featureModerator);

			// variables needed to set the status for this feature request
			if ($featureModerator) {
				$template->assign('FeatureRequestId', $var['RequestID']);
				$template->assign('FeatureRequestStatusFormHREF', Page::create('feature_request_vote_processing.php')->href());
			}

			$featureRequestComments = [];
			foreach ($dbResult->records() as $dbRecord) {
				$commentID = $dbRecord->getInt('comment_id');
				$featureRequestComments[$commentID] = [
										'CommentID' => $commentID,
										'Message' => $dbRecord->getString('text'),
										'Time' => date($account->getDateTimeFormat(), $dbRecord->getInt('posting_time')),
										'Anonymous' => $dbRecord->getBoolean('anonymous'),
				];
				if ($featureModerator || !$dbRecord->getBoolean('anonymous')) {
					$featureRequestComments[$commentID]['PosterAccount'] = SmrAccount::getAccount($dbRecord->getInt('poster_id'));
				}
			}
			$template->assign('Comments', $featureRequestComments);
		}

		$container = Page::create('feature_request_comment_processing.php', $var);
		$template->assign('FeatureRequestCommentFormHREF', $container->href());
