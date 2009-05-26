<?
if (!Globals::isFeatureRequestOpen())
{
	create_error('Feature requests are currently not being accepted.');
	return;
}

$template->assign('PageTopic','FEATURE REQUEST');

$featureVotes = array();
$db->query('SELECT * FROM account_votes_for_feature WHERE account_id = '.SmrSession::$account_id);
while($db->nextRecord())
	$featureVotes[$db->getField('feature_request_id')] = $db->getField('vote_type');

$db->query('SELECT * ' .
			'FROM feature_request ' .
			'ORDER BY feature_request_id');
if ($db->getNumRows() > 0)
{
	$featureModerator = $account->hasPermission(PERMISSION_MODERATE_FEATURE_REQUEST);
	$template->assign('FeatureModerator',$featureModerator);
	$template->assign('FeatureRequestVoteFormHREF',SmrSession::get_new_href(create_container('feature_request_vote_processing.php', '')));

	$db2 = new SmrMySqlDatabase();
	$featureRequests = array();
	while ($db->nextRecord())
	{
		$featureRequestID = $db->getField('feature_request_id');
		$featureRequests[$featureRequestID] = array(
								'RequestID' => $featureRequestID,
								'Message' => $db->getField('feature'),
								'Votes' => array('FAVOURITE'=>0,'YES'=>0,'NO'=>0),
								'VotedFor' => isset($featureVotes[$featureRequestID]) ? $featureVotes[$featureRequestID] : false
		);
		if($featureModerator)
			$featureRequests[$featureRequestID]['RequestAccount'] =& SmrAccount::getAccount($db->getField('submitter_id'));
		
		$db2->query('SELECT COUNT(*), vote_type ' .
					  'FROM account_votes_for_feature ' .
					  'WHERE feature_request_id='.$featureRequestID .
					  ' GROUP BY vote_type');
		while($db2->nextRecord())
		{
			
			$featureRequests[$featureRequestID]['Votes'][$db2->getField('vote_type')] = $db2->getField('COUNT(*)');
		}
	}
	$template->assignByRef('FeatureRequests',$featureRequests);
}

$template->assign('FeatureRequestFormHREF',SmrSession::get_new_href(create_container('feature_request_processing.php', '')));
?>