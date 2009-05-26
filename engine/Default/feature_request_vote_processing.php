<?
if($_REQUEST['action']=='Vote')
{
	$db->query('DELETE FROM account_votes_for_feature WHERE account_id='.SmrSession::$account_id);
	if(is_array($_REQUEST['vote']))
	{
		$query = 'INSERT INTO account_votes_for_feature VALUES ';
		foreach($_REQUEST['vote'] as $requestID => $vote)
		{
			$query.='('.SmrSession::$account_id.', '.$db->escapeNumber($requestID).','.$db->escapeString($vote).'),';
		}
		$db->query(substr($query,0,-1));
	}
	if(!empty($_REQUEST['favourite']) && is_numeric($_REQUEST['favourite']))
		$db->query('REPLACE INTO account_votes_for_feature VALUES('.SmrSession::$account_id.', '.$db->escapeNumber($_REQUEST['favourite']).',\'FAVOURITE\')');
	
	forward(create_container('skeleton.php', 'feature_request.php'));
}
else if($_REQUEST['action']=='Delete')
{
	if(empty($_REQUEST['delete']))
		create_error('You have to select a feature');
	if(!$account->hasPermission(PERMISSION_MODERATE_FEATURE_REQUEST))
		create_error('You do not have permission to do that');
		
	$db->query('DELETE FROM feature_request WHERE feature_request_id IN ('.$db->escapeArray($_REQUEST['delete']).')');
	forward(create_container('skeleton.php', 'feature_request.php'));
}
?>