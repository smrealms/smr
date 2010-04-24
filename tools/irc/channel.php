<?php

function channel_join($fp, $rdata) {

	global $channel;

	if (preg_match('/^:(.*)!(.*)@(.*)\sJOIN\s:'.$channel.'\s$/i', $rdata, $msg)) {

		echo_r($msg);
		if ($msg[1] == 'MrSpock' && $msg[2] == '~foo' && $msg[3] == 'jfwmodule.intershop.de')
			fputs($fp, 'PRIVMSG '.$channel.' :The creator! The God! He\'s among us! Praise him!'.EOL);

		$db = new SmrMySqlDatabase();

		$db->query('INSERT irc_logged_in (nick, user, host)' .
				   ' values ('.$db->escapeString($msg[1]).','.$db->escapeString($msg[2]).','.$db->escapeString($msg[3]).')');
						 
		$db->query('SELECT * FROM irc_seen WHERE nick LIKE '.$db->escape_string('%'.$msg[1].'%'));

		// exiting nick?
		if($db->nextRecord())
		{

			$seen_id = $db->getField('seen_id');

			$db->query('UPDATE irc_seen SET signed_on = ' . time() . ' WHERE seen_id = '.$seen_id);

		// new nick?
		}
		else
		{

			$nick		= serialize(array($msg[1]));
			$user		= $msg[2];
			$host		= $msg[3];
			$signed_on	= time();

			$db->query('INSERT INTO irc_seen (nick, user, host, signed_on) VALUES('.$db->escapeString($nick).', '.$db->escapeString($user).', '.$db->escapeString($host).', '.$signed_on.')');

		}

		return true;

	}

	return false;

}

/**
 * Someone changed his nick
 */
function channel_nick($fp, $rdata) {

	global $channel;

	if (preg_match('/^:(.*)!(.*)@(.*)\sNICK\s:(.*)\s$/i', $rdata, $msg)) {

		echo_r($msg);

		// database object
		$db = new SmrMySqlDatabase();
		$db2 = new SmrMySqlDatabase();

		// update nick if he's logged in
		$db->query('UPDATE irc_logged_in ' .
				   'SET nick = '.$db->escapeString($msg[4]).' ' .
				   'WHERE nick = '.$db->escapeString($msg[1]).' AND ' .
						 'user = '.$db->escapeString($msg[2]).' AND ' .
						 'host = '.$db->escapeString($msg[3]));

		// update seen stats
		$db->query('SELECT * FROM irc_seen ' .
				   'WHERE user = '.$db->escapeString($msg[2]).' AND ' .
						 'host = '.$db->escapeString($msg[3]));
		while($db->nextRecord()) {

			$seen_id = $db->getField('seen_id');
			$nick_list = unserialize($db->getField('nick'));

			// add this nick if it isn't in current nicklist
			if (!array_search($msg[1], $nick_list))
			{

				array_push($nick_list, $msg[4]);
				$db2->query('UPDATE irc_seen ' .
							'SET nick = ' . $db->escape_string(serialize($nick_list)) . ' ' .
							'WHERE seen_id = '.$seen_id);

			}

		}

		return true;

	}

	return false;

}

function channel_part($fp, $rdata) {

	global $channel;

	if (preg_match('/^:(.*)!(.*)@(.*)\sPART\s'.$channel.'\s$/i', $rdata, $msg)) {
		echo_r($msg);
		// delete this user from the active session

		// database object
		$db = new SmrMySqlDatabase();

		// avoid that some1 uses another one nick
		$db->query('DELETE FROM irc_logged_in ' .
				   'WHERE nick = '.$db->escapeString($msg[1]).' AND ' .
						 'user = '.$db->escapeString($msg[2]).' AND ' .
						 'host = '.$db->escapeString($msg[3]));

		$db->query('SELECT * FROM irc_seen WHERE nick LIKE '.$db->escape_string('%'.$msg[1].'%'));

		// exiting nick?
		if($db->nextRecord()) {

			$seen_id = $db->getField('seen_id');

			$db->query('UPDATE irc_seen SET signed_off = ' . time() . ' WHERE seen_id = '.$seen_id);

		// new nick?
		}
		else
		{

			$nick		= serialize(array($msg[1]));
			$user		= $msg[2];
			$host		= $msg[3];
			$signed_off	= time();

			$db->query('INSERT INTO irc_seen (nick, user, host, signed_off) VALUES('.$db->escapeString($nick).', '.$db->escapeString($user).', '.$db->escapeString($host).', '.$signed_off.')');

		}

		return true;

	}

	return false;

}


function channel_who($fp, $rdata)
{
	global $channel,$nick;
	//  /^:( SERVER )\s352'.$nick.'\s'.$channel.'\s( USER )\s( HOST )\s( SERVER )\s( NICK )\s( USER_MODES? irc op/channel op for sure )\s( :(int) changes depending on server? )\s( REAL_NAME )$/i'
//if (preg_match('/^:(.*)\s352\s'.$nick.'\s'.$channel.'\s([^\s]*)\s(.*?)\s([^\s]*)\s([^\s]*)\s([^\s]*)\s([^\s]*)\s(.*)$/i', $rdata, $msg))
		if (preg_match('/^:(.*?) 352 '.$nick.' '.$channel.' (.*?) (.*?) (.*?) (.*?) (.*?) (.*?) (.*?)$/i', $rdata, $msg))
	{
		echo_r($msg);
//		sleep(8);
		$db = new SmrMySqlDatabase();

		$db->query('INSERT IGNORE INTO irc_logged_in (nick, user, host)' .
				   ' values ('.$db->escapeString($msg[5]).','.$db->escapeString($msg[2]).','.$db->escapeString($msg[3]).')');
		
		$db->query('SELECT * FROM irc_seen WHERE nick LIKE '.$db->escape_string('%'.$msg[5].'%'));

		// exiting nick?
		if($db->nextRecord())
		{

			$seen_id = $db->getField('seen_id');

			$db->query('UPDATE irc_seen SET signed_on = ' . time() . ' WHERE seen_id = '.$seen_id);

		// new nick?
		}
		else
		{

			$nick		= serialize(array($msg[5]));
			$user		= $msg[2];
			$host		= $msg[3];
			$signed_on	= time();

			$db->query('INSERT INTO irc_seen (nick, user, host, signed_on) VALUES('.$db->escapeString($nick).', '.$db->escapeString($user).', '.$db->escapeString($host).', '.$signed_on.')');

		}

		return true;

	}

	return false;

}

?>