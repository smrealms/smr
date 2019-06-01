<?php
if ($_REQUEST['action'] == 'Vote') {
	if ($account->getAccountID() == ACCOUNT_ID_NHL) {
		create_error('This account is not allowed to cast a vote!');
	}
	if (is_array($_REQUEST['vote'])) {
		$query = 'REPLACE INTO account_votes_for_feature VALUES ';
		foreach ($_REQUEST['vote'] as $requestID => $vote) {
			$query .= '(' . $db->escapeNumber($account->getAccountID()) . ', ' . $db->escapeNumber($requestID) . ',' . $db->escapeString($vote) . '),';
		}
		$db->query(substr($query, 0, -1));
	}
	if (!empty($_REQUEST['favourite']) && is_numeric($_REQUEST['favourite']))
		$db->query('REPLACE INTO account_votes_for_feature VALUES(' . $db->escapeNumber($account->getAccountID()) . ', ' . $db->escapeNumber($_REQUEST['favourite']) . ',\'FAVOURITE\')');

	forward(create_container('skeleton.php', 'feature_request.php'));
}
else if ($_REQUEST['action'] == 'Set Status') {
	if (empty($_REQUEST['status'])) {
		create_error('You have to select a status to set');
	}
	$status = $_REQUEST['status'];
	if (empty($_REQUEST['set_status_ids']))
		create_error('You have to select a feature');
	if (!$account->hasPermission(PERMISSION_MODERATE_FEATURE_REQUEST))
		create_error('You do not have permission to do that');

	$db->query('UPDATE feature_request fr SET status = ' . $db->escapeString($status) . '
			, fav = (
				SELECT COUNT(feature_request_id)
				FROM account_votes_for_feature
				WHERE feature_request_id=fr.feature_request_id
					AND vote_type=' . $db->escapeString('FAVOURITE') . '
			)
			, yes = (
				SELECT COUNT(feature_request_id)
				FROM account_votes_for_feature
				WHERE feature_request_id=fr.feature_request_id
					AND vote_type IN (' . $db->escapeString('YES') . ',' . $db->escapeString('FAVOURITE') . ')
			)
			, no = (
				SELECT COUNT(feature_request_id)
				FROM account_votes_for_feature
				WHERE feature_request_id=fr.feature_request_id
					AND vote_type=' . $db->escapeString('NO') . '
			)
			WHERE feature_request_id IN (' . $db->escapeArray($_REQUEST['set_status_ids']) . ')');
	foreach ($_REQUEST['set_status_ids'] as $featureID) {
		$db->query('INSERT INTO feature_request_comments (feature_request_id, poster_id, posting_time, anonymous, text)
					VALUES(' . $db->escapeNumber($featureID) . ', ' . $db->escapeNumber($account->getAccountID()) . ',' . $db->escapeNumber(TIME) . ',' . $db->escapeBoolean(false) . ',' . $db->escapeString($status) . ')');
	}

	forward(create_container('skeleton.php', 'feature_request.php'));
}
