<?php
$db = new SmrMySqlDatabase();
$db->query('SELECT 1 FROM account JOIN announcement ON last_login < time ' .
		   'WHERE account_id = '.SmrSession::$account_id.'  LIMIT 1');

$container = array();

// do we have announcements?
if ($db->getNumRows() != 0)
{
	$container['url'] = 'skeleton.php';
	$container['body'] = 'announcements.php';
}
else
	$container['url'] = 'logged_in.php';

forward($container);

?>