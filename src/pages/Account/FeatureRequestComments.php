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
	public function __construct(
		private readonly int $featureRequestID,
		private readonly FeatureRequest $previousPage,
	) {}

	public function build(Account $account, Template $template): void {
		if (!Globals::isFeatureRequestOpen()) {
			create_error('Feature requests are currently not being accepted.');
		}

		$template->pageTopic = 'Feature Request Comments';

		$db = Database::getInstance();
		$dbResult = $db->read('SELECT *
					FROM feature_request
					JOIN feature_request_comments USING(feature_request_id)
					WHERE feature_request_id = :feature_request_id
					ORDER BY comment_id ASC', [
			'feature_request_id' => $db->escapeNumber($this->featureRequestID),
		]);

		// variables needed to set the status for this feature request
		$featureModerator = $account->hasPermission(PERMISSION_MODERATE_FEATURE_REQUEST);
		$featureRequestStatusFormPage = $featureModerator ?
			new FeatureRequestVoteProcessor($this) : null;

		$featureRequestComments = [];
		foreach ($dbResult->records() as $dbRecord) {
			$commentAccountID = $dbRecord->getInt('poster_id');
			if ($dbRecord->getBoolean('anonymous')) {
				$displayName = 'Anonymous';
			} else {
				$displayName = Account::getAccount($commentAccountID)->getHofDisplayName();
			}
			if ($featureModerator) {
				$commentLogin = Account::getAccount($commentAccountID)->getLogin();
				$displayName .= ' - ' . $commentLogin . ' (' . $commentAccountID . ')';
			}

			$commentID = $dbRecord->getInt('comment_id');
			$featureRequestComments[$commentID] = [
				'CommentID' => $commentID,
				'Message' => $dbRecord->getString('text'),
				'Time' => date($account->getDateTimeFormat(), $dbRecord->getInt('posting_time')),
				'Name' => $displayName,
			];
		}

		$template->pageRenderer = fn() => FeatureRequestCommentsRenderer::render(
			BackHref: $this->previousPage->href(),
			FeatureModerator: $featureModerator,
			FeatureRequestId: $this->featureRequestID,
			FeatureRequestStatusFormPage: $featureRequestStatusFormPage,
			Comments: $featureRequestComments,
			FeatureRequestCommentFormHREF: new FeatureRequestCommentProcessor($this->featureRequestID, $this)->href(),
		);
	}

}
