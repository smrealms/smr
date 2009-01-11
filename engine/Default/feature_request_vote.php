<?

// for which feature we currently vote?
$db->query('SELECT * FROM account_votes_for_feature WHERE account_id = '.SmrSession::$account_id);
if ($db->nextRecord()) {

	$vote_for_id = $db->getField('feature_request_id');

	// are there more than one vote for this feature?
	$db->query('SELECT * FROM account_votes_for_feature ' .
						'WHERE feature_request_id = '.$vote_for_id);
	if ($db->getNumRows() == 1)
		$db->query('DELETE FROM feature_request WHERE feature_request_id = '.$vote_for_id);

}

$db->query('REPLACE INTO account_votes_for_feature VALUES('.SmrSession::$account_id.', '.$vote.')');

forward(create_container('skeleton.php', 'feature_request.php'));

?>