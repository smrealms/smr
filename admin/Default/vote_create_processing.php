<?php
$question = trim($_REQUEST['question']);
if($_REQUEST['action'] == 'Preview Vote')
{
	$container = create_container('skeleton.php','vote_create.php');
	$container['PreviewVote'] = $question;
	$container['Days'] = $_REQUEST['days'];
	forward($container);
}
$option = trim($_REQUEST['option']);
if($_REQUEST['action'] == 'Preview Option')
{
	$container = create_container('skeleton.php','vote_create.php');
	$container['PreviewOption'] = $option;
	$container['VoteID'] = $_REQUEST['vote'];
	forward($container);
}

if($_REQUEST['action'] == 'Create Vote')
{
	$end = TIME+86400*$_REQUEST['days'];
	
	// put the msg into the database
	$db->query('INSERT INTO voting (question, end) VALUES('.$db->escapeString($question).','.$db->escapeNumber($end).')');
}
else if($_REQUEST['action'] == 'Add Option')
{
	$voteID = $_REQUEST['vote'];
	
	// put the msg into the database
	$db->query('INSERT INTO voting_options (vote_id, text) VALUES('.$db->escapeNumber($voteID).','.$db->escapeString($option).')');
}
forward(create_container('skeleton.php', 'vote_create.php'))

?>