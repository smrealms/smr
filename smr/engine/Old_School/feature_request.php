<?

$smarty->assign('PageTopic','FEATURE REQUEST');

$db->query('SELECT * FROM account_votes_for_feature WHERE account_id = '.SmrSession::$account_id);
if ($db->next_record())
	$feature_vote = $db->f('feature_request_id');

$db->query('SELECT f.feature_request_id AS feature_id, ' .
				  'f.feature AS feature_msg, ' .
				  'f.submitter_id AS submitter_id, ' .
				  'COUNT(v.feature_request_id) AS votes ' .
  				'FROM feature_request f LEFT OUTER JOIN account_votes_for_feature v ON f.feature_request_id = v.feature_request_id ' .
  				'GROUP BY feature_id, feature_msg ' .
  				'ORDER BY votes DESC, feature_id');

if ($db->nf() > 0) {

	$PHP_OUTPUT.=create_echo_form(create_container('feature_request_vote.php', ''));
	$PHP_OUTPUT.=('<p><table cellspacing="0" cellpadding="3" border="0" class="standard" width="100%">');
	$PHP_OUTPUT.=('<tr>');
	$PHP_OUTPUT.=('<th width="30">Votes</th>');
	$PHP_OUTPUT.=('<th>Feature</th>');
	$PHP_OUTPUT.=('<th width="20">&nbsp;</th>');
	$PHP_OUTPUT.=('</tr>');

	while ($db->next_record()) {

		$feature_request_id = $db->f('feature_id');
		$submitter_id = $db->f('submitter_id');
		$message = stripslashes($db->f('feature_msg'));
		$votes = $db->f('votes');

		$PHP_OUTPUT.=('<tr>');
		$PHP_OUTPUT.=('<td valign="top" align="center">'.$votes.'</td>');
		$PHP_OUTPUT.=('<td valign="top">'.$message.'</td>');
		$PHP_OUTPUT.=('<td valign="middle" align="center"><input type="radio" name="vote" value="'.$feature_request_id.'"');
		if ($feature_request_id == $feature_vote) $PHP_OUTPUT.=(' checked');
		$PHP_OUTPUT.=('></td>');
		$PHP_OUTPUT.=('</tr>');

	}

	$PHP_OUTPUT.=('</table></p>');
	$PHP_OUTPUT.=('<div align="right"><input type="submit" value="Vote"></div>');
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