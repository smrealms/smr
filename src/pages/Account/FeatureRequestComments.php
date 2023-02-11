<?php declare(strict_types=1);

namespace Smr\Pages\Account;

use Smr\Account;
use Smr\Database;
use Smr\Globals;
use Smr\Page\AccountPage;
use Smr\Page\ReusableTrait;
use Smr\Template;

class FeatureRequestComments extends AccountPage {

	use ReusableTrait;

	public string $file = 'feature_request_comments.php';

	public function __construct(
		private readonly int $featureRequestID,
		private readonly FeatureRequest $previousPage
	) {}

	public function build(Account $account, Template $template): void {

		if (!Globals::isFeatureRequestOpen()) {
			create_error('Feature requests are currently not being accepted.');
		}

		$template->assign('PageTopic', 'Feature Request Comments');

		$template->assign('BackHref', $this->previousPage->href());

		$db = Database::getInstance();
		$dbResult = $db->read('SELECT *
					FROM feature_request
					JOIN feature_request_comments USING(feature_request_id)
					WHERE feature_request_id = :feature_request_id
					ORDER BY comment_id ASC', [
			'feature_request_id' => $db->escapeNumber($this->featureRequestID),
		]);
		if ($dbResult->hasRecord()) {
			$featureModerator = $account->hasPermission(PERMISSION_MODERATE_FEATURE_REQUEST);
			$template->assign('FeatureModerator', $featureModerator);

			// variables needed to set the status for this feature request
			if ($featureModerator) {
				$template->assign('FeatureRequestId', $this->featureRequestID);
				$template->assign('FeatureRequestStatusFormHREF', (new FeatureRequestVoteProcessor($this))->href());
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
					$featureRequestComments[$commentID]['PosterAccount'] = Account::getAccount($dbRecord->getInt('poster_id'));
				}
			}
			$template->assign('Comments', $featureRequestComments);
		}

		$container = new FeatureRequestCommentProcessor($this->featureRequestID, $this);
		$template->assign('FeatureRequestCommentFormHREF', $container->href());
	}

}
