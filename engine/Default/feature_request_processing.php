<?

if (empty($_REQUEST['feature']))
	create_error('We need at least a feature desciption!');

// add this feature to db
$db->query('INSERT INTO feature_request (feature, submitter_id) ' .
								 'VALUES(' . $db->escape_string(word_filter($_REQUEST['feature']), true) . ', '.SmrSession::$account_id.')');

// vote for this feature
$db->query('INSERT INTO account_votes_for_feature VALUES('.SmrSession::$account_id.', '.$db->getInsertID().',\'YES\')');

forward(create_container('skeleton.php', 'feature_request.php'));

?>