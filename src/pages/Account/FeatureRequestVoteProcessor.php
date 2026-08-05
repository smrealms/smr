<?php declare(strict_types=1);

namespace Smr\Pages\Account;

use Exception;
use Smr\Account;
use Smr\Database;
use Smr\Epoch;
use Smr\Html\Submit;
use Smr\Page\AccountPageProcessor;
use Smr\Request;

class FeatureRequestVoteProcessor extends AccountPageProcessor {

	private const string ACTION = 'action';

	public readonly Submit $actionSetStatus;
	public readonly Submit $actionVote;

	public function __construct(
		private readonly FeatureRequest|FeatureRequestComments $previousPage,
	) {
		$this->actionSetStatus = new Submit(self::ACTION, 'Set Status');
		$this->actionVote = new Submit(self::ACTION, 'Vote');
	}

	public function build(Account $account): never {
		$db = Database::getInstance();

		$action = Request::get(self::ACTION);
		if ($action === $this->actionVote->value) {
			if ($account->isNHL()) {
				create_error('This account is not allowed to cast a vote!');
			}
			if (Request::has('vote')) {
				foreach (Request::getArray('vote') as $requestID => $vote) {
					$db->replace('account_votes_for_feature', [
						'account_id' => $account->getAccountID(),
						'feature_request_id' => $requestID,
						'vote_type' => $vote,
					]);
				}
			}
			if (Request::has('favourite')) {
				$db->replace('account_votes_for_feature', [
					'account_id' => $account->getAccountID(),
					'feature_request_id' => Request::getInt('favourite'),
					'vote_type' => 'FAVOURITE',
				]);
			}

		} elseif ($action === $this->actionSetStatus->value) {
			if (!$account->hasPermission(PERMISSION_MODERATE_FEATURE_REQUEST)) {
				create_error('You do not have permission to do that');
			}
			if (!Request::has('status')) {
				create_error('You have to select a status to set');
			}
			$status = Request::get('status');
			if (!Request::has('set_status_ids')) {
				create_error('You have to select a feature');
			}
			$setStatusIDs = Request::getIntArray('set_status_ids');

			foreach ($setStatusIDs as $featureID) {
				$db->update(
					'feature_request',
					['status' => $status],
					['feature_request_id' => $featureID],
				);
				$db->insert('feature_request_comments', [
					'feature_request_id' => $featureID,
					'poster_id' => $account->getAccountID(),
					'posting_time' => Epoch::time(),
					'anonymous' => $db->escapeBoolean(false),
					'text' => $status,
				]);
			}
		} else {
			throw new Exception('Unknown action: ' . $action);
		}

		$this->previousPage->go();
	}

}
