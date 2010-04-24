<?php

function format_time($time) {

	$hours = floor($time / 3600);
	$minutes = floor(($time - $hours * 3600) / 60);
	$seconds = $time - $hours * 3600 - $minutes * 60;

	$time_str = '';

	if ($hours > 0) {

		if ($hours == 1)
			$time_str .= $hours.' hour';
		else
			$time_str .= $hours.' hours';

		if ($minutes > 0 && $seconds > 0)
			$time_str .= ', ';
		elseif ($minutes > 0 || $seconds > 0)
			$time_str .= ' and ';
		else
			$time_str .= '.';
	}

	if ($minutes > 0) {

		if ($minutes == 1)
			$time_str .= $minutes.' minute';
		else
			$time_str .= $minutes.' minutes';

		if ($seconds > 0)
			$time_str .= ' and ';
	}

	if ($seconds > 0)
		if ($seconds == 1)
			$time_str .= $seconds.' second';
		else
			$time_str .= $seconds.' seconds';

	// esp. if no time left...
	if ($hours == 0 && $minutes == 0 && $seconds == 0)
		$time_str .= '0 seconds';

	return $time_str;

}

function channel_msg_seen($fp, $rdata) {

	global $channel;

	// <Caretaker> MrSpock, Azool (Azool@smrealms.rulez) was last seen quitting #smr
	// 2 days 10 hours 43 minutes ago (05.10. 05:04) stating 'Some people follow their dreams,
	// others hunt them down and mercessly beat them into submission' after spending 1 hour 54 minutes there.

	// MrSpock, do I look like a mirror? ^_^

	// MrSpock, please look a bit closer at the memberlist of this channel.

	if (preg_match('/^:(.*)!(.*)@(.*)\sPRIVMSG\s'.$channel.'\s:!seen\s(.*)\s$/i', $rdata, $msg))
	{
		echo_r($msg);
		if($msg[1]==$msg[4])
		{
			fputs($fp, 'PRIVMSG '.$channel.' :'.$msg[1].', do I look like a mirror?'.EOL);
			return true;
		}
		$db = new SmrMySqlDatabase();
		$db2 = new SmrMySqlDatabase();

		$found = false;

		$db->query('SELECT * FROM irc_logged_in WHERE nick = '.$db->escape_string($msg[4]));
		if($db->nextRecord())
		{
			fputs($fp, 'PRIVMSG '.$channel.' :'.$msg[1].', please look a bit closer at the memberlist of this channel.'.EOL);
			return true;
		}
		$db->query('SELECT * FROM irc_seen WHERE nick LIKE '.$db->escapeString('%'.$msg[4].'%'));
		echo_r('SELECT * FROM irc_seen WHERE nick LIKE '.$db->escapeString('%'.$msg[4].'%'));
		while($db->nextRecord())
		{
			$nick_list	= unserialize($db->getField('nick'));

			// search for the nick in the nicklist
			if (array_search($msg[4], $nick_list) !== false)
			{
				$user		= $db->getField('user');
				$host		= $db->getField('host');
				$signed_on	= $db->getField('signed_on');
				$signed_off	= $db->getField('signed_off');

				fputs($fp, 'PRIVMSG '.$channel.' :'.$msg[1].', '.$msg[4].' ('.$user.'@'.$host.') was last seen quitting '.$channel.' ' . format_time($signed_off) . ' ago after spending ' . format_time($signed_off - $signed_on) . ' there.'.EOL);
				return true;
			}
		}
		
		fputs($fp, 'PRIVMSG '.$channel.' :'.$msg[1].', I don\'t remember seeing '.$msg[4].'.'.EOL);
		return true;

	}

	return false;

}

?>