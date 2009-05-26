<?
if (!Globals::isFeatureRequestOpen())
{
	create_error('Feature requests are currently not being accepted.');
	return;
}

$template->assign('PageTopic','FEATURE REQUEST');

$db->query('SELECT * FROM account_votes_for_feature WHERE account_id = '.SmrSession::$account_id);
if ($db->nextRecord())
	$feature_vote = $db->getField('feature_request_id');

$db->query('SELECT f.feature_request_id AS feature_id, ' .
				  'f.feature AS feature_msg, ' .
				  'f.submitter_id AS submitter_id, ' .
				  'COUNT(v.feature_request_id) AS votes ' .
  				'FROM feature_request f LEFT OUTER JOIN account_votes_for_feature v ON f.feature_request_id = v.feature_request_id ' .
  				'GROUP BY feature_id, feature_msg ' .
  				'ORDER BY votes DESC, feature_id');

if ($db->getNumRows() > 0)
{
	$featureModerator = $account->hasPermission(PERMISSION_MODERATE_FEATURE_REQUEST);
	$template->assign('FeatureModerator',$featureModerator);
	$template->assign('FeatureRequestVoteFormHREF',SmrSession::get_new_href(create_container('feature_request_vote_processing.php', '')));

	$featureRequests = array();
	while ($db->nextRecord())
	{
		$featureRequestID = $db->getField('feature_id');
		$featureRequests[$featureRequestID] = array(
								'RequestID' => $featureRequestID,
								'Message' => $db->getField('feature_msg'),
								'Votes' => $db->getField('votes'),
								'VotedFor' => $featureRequestID == $feature_vote
		);
		if($featureModerator)
			$featureRequests[$featureRequestID]['RequestAccount'] =& SmrAccount::getAccount($db->getField('submitter_id'));
	}
	$template->assignByRef('FeatureRequests',$featureRequests);
}

$template->assign('FeatureRequestFormHREF',SmrSession::get_new_href(create_container('feature_request_processing.php', '')));
?>