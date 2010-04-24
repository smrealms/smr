<?php

function private_msg_login($fp, $rdata) {

	global $nick;

	if (preg_match('/^:(.*)!(.*)@(.*)\sPRIVMSG\s'.$nick.'\s:login\s(.*)\s(.*)\s(.*)\s$/i', $rdata, $msg)) {

		echo_r($msg);
		$db = new SmrMySqlDatabase();

		$db->query('SELECT * FROM account WHERE login = '.$db->escapeString($msg[4]));
		if ($db->nextRecord()) {

			$account_id		= $db->getField('account_id');
			$password		= $db->getField('password');

			// does pwd match?
			if ($msg[5] != $password) {

				fputs($fp, 'NOTICE '.$msg[1].' :User doesn\'t exist!'.EOL);
				return true;

			}

			// check if this game exist
			$db->query('SELECT * FROM game WHERE game_id = '.$msg[6]);
			if (!$db->getNumRows()) {

				fputs($fp, 'NOTICE '.$msg[1].' :Game doesn\'t exist!'.EOL);
				return true;

			}

			// registering this user
			$db->query('REPLACE INTO irc_logged_in ' .
					   '(account_id, game_id, nick, user, host, time) ' .
					   'VALUES('.$account_id.', '.$msg[6].', '.$db->escapeString($msg[1]).', '.$db->escapeString($msg[2]).', '.$db->escapeString($msg[3]).', ' . time() . ')');

			fputs($fp, 'NOTICE '.$msg[1].' :Password accepted - you are now recognized.'.EOL);

		} else
			fputs($fp, 'NOTICE '.$msg[1].' :User doesn\'t exist!'.EOL);

		return true;

	}

	return false;

}

?>