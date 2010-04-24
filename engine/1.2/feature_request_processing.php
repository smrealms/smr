<?php

if (empty($feature))
	create_error("We need at least a feature desciption!");

// add this feature to db
$db->query("INSERT INTO feature_request (feature, submitter_id) " .
								 "VALUES(" . format_string($feature, true) . ", ".SmrSession::$old_account_id.")");

// which number did it get?
$vote_new_id = $db->insert_id();

// for which feature we currently vote?
$db->query("SELECT * FROM account_votes_for_feature WHERE account_id = ".SmrSession::$old_account_id);
if ($db->next_record()) {

	$vote_old_id = $db->f("feature_request_id");

	// are there more than one vote for this feature?
	$db->query("SELECT * FROM account_votes_for_feature " .
						"WHERE feature_request_id = $vote_old_id");

	// no? delete it.
	if ($db->nf() == 1)
		$db->query("DELETE FROM feature_request WHERE feature_request_id = $vote_old_id");

}

// vote for this feature
$db->query("REPLACE INTO account_votes_for_feature VALUES(".SmrSession::$old_account_id.", $vote_new_id)");

forward(create_container("skeleton.php", "feature_request.php"));

?>