<?php
if (!Globals::isFeatureRequestOpen())
	create_error('Feature requests are currently not being accepted.');

$template->assign('PageTopic','Feature Request');

$container = $var;
$container['implemented'] = true;
$template->assign('ViewImplementedFeaturesHref',SmrSession::get_new_href($container));

$onlyImplemented = isset($var['implemented'])?$var['implemented']===true:false;
$template->assign('OnlyImplemented',$onlyImplemented);

if(!$onlyImplemented)
{
	$featureVotes = array();
	$db->query('SELECT * FROM account_votes_for_feature WHERE account_id = '.SmrSession::$account_id);
	while($db->nextRecord())
		$featureVotes[$db->getField('feature_request_id')] = $db->getField('vote_type');
}

$db->query('SELECT * ' .
			'FROM feature_request ' .
			'JOIN feature_request_comments USING(feature_request_id)' .
			'WHERE comment_id = 1 ' .
			'AND implemented = ' . $db->escapeBoolean($onlyImplemented) .
			'ORDER BY feature_request_id DESC');
if ($db->getNumRows() > 0)
{
	$featureModerator = $account->hasPermission(PERMISSION_MODERATE_FEATURE_REQUEST);
	$template->assign('FeatureModerator',$featureModerator);
	$template->assign('FeatureRequestVoteFormHREF',SmrSession::get_new_href(create_container('feature_request_vote_processing.php', '')));

	$commentsContainer = $var;
	$commentsContainer['body'] = 'feature_request_comments.php';
	$db2 = new SmrMySqlDatabase();
	$featureRequests = array();
	while ($db->nextRecord())
	{
		$featureRequestID = $db->getField('feature_request_id');
		$featureRequests[$featureRequestID] = array(
								'RequestID' => $featureRequestID,
								'Message' => $db->getField('text'),
								'Votes' => array('FAVOURITE'=>$db->getField('fav'),'YES'=>$db->getField('yes'),'NO'=>$db->getField('no')),
								'VotedFor' => isset($featureVotes[$featureRequestID]) ? $featureVotes[$featureRequestID] : false
		);
		if($featureModerator)
			$featureRequests[$featureRequestID]['RequestAccount'] =& SmrAccount::getAccount($db->getField('poster_id'));
		
		if(!$onlyImplemented)
		{
			$db2->query('SELECT COUNT(*), vote_type ' .
						  'FROM account_votes_for_feature ' .
						  'WHERE feature_request_id='.$featureRequestID .
						  ' GROUP BY vote_type');
			while($db2->nextRecord())
			{
				$featureRequests[$featureRequestID]['Votes'][$db2->getField('vote_type')] = $db2->getField('COUNT(*)');
			}
		}
		$db2->query('SELECT COUNT(*) ' .
					  'FROM feature_request_comments ' .
					  'WHERE feature_request_id='.$featureRequestID);
		while($db2->nextRecord())
		{
			$featureRequests[$featureRequestID]['Comments'] = $db2->getField('COUNT(*)');
		}
		$commentsContainer['RequestID'] = $featureRequestID;
		$featureRequests[$featureRequestID]['CommentsHREF'] = SmrSession::get_new_href($commentsContainer);
	}
	$template->assignByRef('FeatureRequests',$featureRequests);
}

$template->assign('FeatureRequestFormHREF',SmrSession::get_new_href(create_container('feature_request_processing.php', '')));
?>