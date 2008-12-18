<?

$account_id = $_REQUEST['account_id'];
$exception = $_REQUEST['exception'];
if (!is_array($account_id))
	create_error('Please check the boxes next to the names you wish to open.');
	
$action = $_REQUEST['action'];
if ($action == 'Reopen and add to exceptions') {

    foreach ($account_id as $id) {

        $curr_exception = $exception[$id];
        $db->query('DELETE FROM account_is_closed WHERE account_id = '.$id);
        $db->query('REPLACE INTO account_exceptions (account_id, reason) ' .
                        'VALUES ('.$id.', '.$db->escapeString($curr_exception).')');

    }
} else {

    foreach ($account_id as $id) {

        $db->query('DELETE FROM account_is_closed WHERE account_id = '.$id);

    }

}

forward(create_container('skeleton.php', 'game_play.php'));


?>