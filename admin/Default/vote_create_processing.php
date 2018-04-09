<?php
$question = trim($_REQUEST['question']);
if($_REQUEST['action'] == 'Preview Vote') {
	$container = create_container('skeleton.php','vote_create.php');
	$container['PreviewVote'] = $question;
	$container['Days'] = $_REQUEST['days'];
	forward($container);
}
$option = trim($_REQUEST['option']);
if($_REQUEST['action'] == 'Preview Option') {
	$container = create_container('skeleton.php','vote_create.php');
	$container['PreviewOption'] = $option;
	$container['VoteID'] = $_REQUEST['vote'];
	forward($container);
}

if($_REQUEST['action'] == 'Create Vote') {
	if(empty($question))
		create_error('You have to specify a vote message.');
	if(empty($_REQUEST['days']))
		create_error('You have to specify the amount of time to run the vote for.');
	if(!is_numeric($_REQUEST['days']))
		create_error('The vote runtime must be a number.');
	$end = TIME+86400*$_REQUEST['days'];
	
	// put the msg into the database
	$db->query('INSERT INTO voting (question, end) VALUES('.$db->escapeString($question).','.$db->escapeNumber($end).')');
}
else if($_REQUEST['action'] == 'Add Option') {
	if(empty($option))
		create_error('You have to specify an option message.');
	if(empty($_REQUEST['vote']))
		create_error('You have to select a vote to add the option to.');
	if(!is_numeric($_REQUEST['vote']))
		create_error('Vote ID must be a number.');
	
	// put the msg into the database
	$db->query('INSERT INTO voting_options (vote_id, text) VALUES('.$db->escapeNumber($_REQUEST['vote']).','.$db->escapeString($option).')');
}
forward(create_container('skeleton.php', 'vote_create.php'))
