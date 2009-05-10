<?

if($_REQUEST['action'] == 'Create')
{
	$question = $_REQUEST['question'];
	$end = TIME+86400*$_REQUEST['days'];
	
	// put the msg into the database
	$db->query('INSERT INTO voting (question, end) VALUES('.$db->escapeString($question).','.$db->escapeNumber($end).')');
}
else if($_REQUEST['action'] == 'Add Option')
{
	$option = $_REQUEST['option'];
	$voteID = $_REQUEST['vote'];
	
	// put the msg into the database
	$db->query('INSERT INTO voting_options (vote_id, text) VALUES('.$db->escapeNumber($voteID).','.$db->escapeString($option).')');
}
forward(create_container('skeleton.php', 'vote_create.php'))

?>