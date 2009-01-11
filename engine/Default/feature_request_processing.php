<?

if (empty($feature))
	create_error('We need at least a feature desciption!');

// add this feature to db
$db->query('INSERT INTO feature_request (feature, submitter_id) ' .
								 'VALUES(' . $db->escape_string($feature, true) . ', '.SmrSession::$account_id.')');

// which number did it get?
$vote_new_id = $db->getInsertID();

// for which feature we currently vote?
$db->query('SELECT * FROM account_votes_for_feature WHERE account_id = '.SmrSession::$account_id);
if ($db->nextRecord()) {

	$vote_old_id = $db->getField('feature_request_id');

	// are there more than one vote for this feature?
	$db->query('SELECT * FROM account_votes_for_feature ' .
						'WHERE feature_request_id = '.$vote_old_id);

	// no? delete it.
	if ($db->getNumRows() == 1)
		$db->query('DELETE FROM feature_request WHERE feature_request_id = '.$vote_old_id);

}

// vote for this feature
$db->query('REPLACE INTO account_votes_for_feature VALUES('.SmrSession::$account_id.', '.$vote_new_id.')');

forward(create_container('skeleton.php', 'feature_request.php'));

?>