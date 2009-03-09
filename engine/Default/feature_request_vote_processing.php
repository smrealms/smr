<?
if($_REQUEST['action']=='Vote')
{
	if(empty($_REQUEST['vote']))
		create_error('You have to select a feature');
	// for which feature we currently vote?
	$db->query('SELECT * FROM account_votes_for_feature WHERE account_id = '.SmrSession::$account_id);
	if ($db->nextRecord())
	{
		$vote_for_id = $db->getField('feature_request_id');
	
		// are there more than one vote for this feature?
		$db->query('SELECT * FROM account_votes_for_feature ' .
							'WHERE feature_request_id = '.$vote_for_id);
//		if ($db->getNumRows() == 1)
//			$db->query('DELETE FROM feature_request WHERE feature_request_id = '.$vote_for_id);
	}
	
	$db->query('REPLACE INTO account_votes_for_feature VALUES('.SmrSession::$account_id.', '.$_REQUEST['vote'].')');
	
	forward(create_container('skeleton.php', 'feature_request.php'));
}
else if($_REQUEST['action']=='Delete')
{
	if(empty($_REQUEST['delete']))
		create_error('You have to select a feature');
	$db->query('SELECT *
				FROM account_has_permission
				WHERE account_id = '.SmrSession::$account_id.' AND
					  permission_id = '.PERMISSION_MODERATE_FEATURE_REQUEST);
	if(!$db->nextRecord())
		create_error('You do not have permission to do that');
		
	$db->query('DELETE FROM feature_request WHERE feature_request_id IN ('.$db->escapeArray($_REQUEST['delete']).')');
	forward(create_container('skeleton.php', 'feature_request.php'));
}
?>