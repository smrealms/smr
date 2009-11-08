<?php

//////////////////////////////////////////////////
//
//	Script:		vote_processing.php
//	Purpose:	Registers Votes
//
//////////////////////////////////////////////////

if (!is_numeric($_REQUEST['vote']))
	create_error('You must choose an option.');
$db->query('REPLACE INTO voting_results (account_id, vote_id, option_id) VALUES (' . $account->getAccountID() . ',' . $var['vote_id'] . ',' . $_REQUEST['vote'] . ')');
$var['url'] = 'skeleton.php';
forward($var);
?>