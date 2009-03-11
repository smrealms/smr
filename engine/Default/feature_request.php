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
	$DELETE_ALLOWED=$account->hasPermission(PERMISSION_MODERATE_FEATURE_REQUEST);
	$PHP_OUTPUT.=create_echo_form(create_container('feature_request_vote_processing.php', ''));
	$PHP_OUTPUT.=('<p><table class="standard" width="100%">');
	$PHP_OUTPUT.=('<tr>');
	$PHP_OUTPUT.=('<th width="30">Votes</th>');
	$PHP_OUTPUT.=('<th>Feature</th>');
	$PHP_OUTPUT.=('<th width="20">&nbsp;</th>');
	if($DELETE_ALLOWED)
		$PHP_OUTPUT.=('<th width="20">&nbsp;</th>');
	$PHP_OUTPUT.=('</tr>');

	while ($db->nextRecord())
	{
		$feature_request_id = $db->getField('feature_id');
		$submitter_id = $db->getField('submitter_id');
		$message = stripslashes($db->getField('feature_msg'));
		$votes = $db->getField('votes');

		$PHP_OUTPUT.=('<tr>');
		$PHP_OUTPUT.=('<td valign="top" align="center">'.$votes.'</td>');
		$PHP_OUTPUT.=('<td valign="top">'.$message.'</td>');
		$PHP_OUTPUT.=('<td valign="middle" align="center"><input type="radio" name="vote" value="'.$feature_request_id.'"');
		if ($feature_request_id == $feature_vote) $PHP_OUTPUT.=(' checked');
		$PHP_OUTPUT.=('></td>');
		if($DELETE_ALLOWED)
			$PHP_OUTPUT.=('<td valign="middle" align="center"><input type="checkbox" name="delete[]" value="'.$feature_request_id.'"></td>');
		$PHP_OUTPUT.=('</tr>');
	}

	$PHP_OUTPUT.=('</table></p>');
	$PHP_OUTPUT.='<div align="right"><input type="submit" name="action" value="Vote">';
	
	if($DELETE_ALLOWED)
		$PHP_OUTPUT.=('&nbsp;<input type="submit" name="action" value="Delete">');
	$PHP_OUTPUT.='</div>';
	$PHP_OUTPUT.=('</form>');

}

$PHP_OUTPUT.=('<p>');
$PHP_OUTPUT.=create_echo_form(create_container('feature_request_processing.php', ''));
$PHP_OUTPUT.=('<table border="0" cellpadding="5">');
$PHP_OUTPUT.=('<tr>');
$PHP_OUTPUT.=('<td align="center">Please describe the feature here:</td>');
$PHP_OUTPUT.=('</tr>');
$PHP_OUTPUT.=('<tr>');
$PHP_OUTPUT.=('<td align="center"><textarea name="feature" id="InputFields" style="width:350px;height:100px;"></textarea></td>');
$PHP_OUTPUT.=('</tr>');
$PHP_OUTPUT.=('<tr>');
$PHP_OUTPUT.=('<td align="center">');
$PHP_OUTPUT.=create_submit('Submit New Feature');
$PHP_OUTPUT.=('</td>');
$PHP_OUTPUT.=('</tr>');
$PHP_OUTPUT.=('</table>');
$PHP_OUTPUT.=('</form></p>');

?>