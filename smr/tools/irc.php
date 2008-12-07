#!/usr/bin/php -q
<?php
define('IRC_BOT_SOCKET_MAX_LENGTH',512);

function echo_r($message)
{
	if(is_array($message))
	{
		foreach($message as $msg)
			echo_r($msg);
	}
	else
		echo $message.EOL;
}

// config file
include( realpath(dirname(__FILE__)) . '/../htdocs/config.inc');

include($LIB . 'global/smr_db.inc');

include($ENGINE . '/Old_School/smr.inc');

$channel = '#smr-beta';
$nick = 'Rawr';
	
$sockets = array();
if (socket_create_pair(AF_UNIX, SOCK_STREAM, 0, $sockets) === false)
	echo_r('socket_create_pair failed. Reason: '.socket_strerror(socket_last_error()));
$pid = pcntl_fork();
if(!$pid)
{
	doIRCListen($sockets[0]);
}
else
{
	doLocalListen($sockets[1]);
}

function fill_string($str, $length) {

	while (strlen($str) < $length)
		$str .= ' ';

	return $str;

}

function doLocalListen($socket)
{
	unlink(IRC_BOT_SOCKET);
	$sock = socket_create(AF_UNIX, SOCK_STREAM, 0) or die('Could not create socket');
	// Bind the socket to an address/port
	socket_bind($sock, IRC_BOT_SOCKET) or die('Could not bind to address');
	chmod(IRC_BOT_SOCKET, 0777);
	socket_listen($sock);
	while($listenSock = socket_accept($sock))
	{
		while(($data = socket_read($listenSock, IRC_BOT_SOCKET_MAX_LENGTH, PHP_NORMAL_READ))!== false)
		{
			if($data!='')
			{
				socket_write($socket, $data.EOL);
			}
		}
	}
}
function doIRCListen($socket)
{
	global $channel, $nick;

	$address = 'irc.VJTD3.com';
	$port = 6667;
	$pass = 'botpassrawr123';
	
	echo_r('Connecting to '.$address);
	
	// include all sub files
	require_once('irc/help.php');
	require_once('irc/rank.php');
	require_once('irc/ship.php');
	require_once('irc/server.php');
	require_once('irc/weapon.php');
	require_once('irc/seen.php');
	require_once('irc/level.php');
	require_once('irc/channel.php');
	require_once('irc/login.php');

	$fp = fsockopen($address, $port);
	if ($fp)
	{
		stream_set_blocking($fp, FALSE);
		echo_r('Socket '.$fp.' is connected... Identifying...');
	
		fputs($fp, 'NICK '.$nick.EOL);
		fputs($fp, 'USER rawr snoopy vjtd3 :Page Test Bot'.EOL);
	
		// join our channel
		fputs($fp, 'NICKSERV IDENTIFY '.$pass.EOL);
		fputs($fp, 'JOIN '.$channel.EOL);
		sleep(1);
		fputs($fp, 'WHO '.$channel.EOL);
	
		// database object
		$db = new SMR_DB();
	
		// avoid that some1 uses another one nick
		$db->query('TRUNCATE irc_logged_in');
		while (!feof($fp))
		{
//			if(($data = socket_read($socket, IRC_BOT_SOCKET_MAX_LENGTH, PHP_NORMAL_READ)) !== false)
//			{
//				if($data !== '')
//				{
//					echo_r('Recieved to send: '.$data);
////					fputs($fp, $data.EOL);
//					fputs($fp, 'PRIVMSG '.$channel.' :'.$data.EOL);
//				}
//			}
			
			$rdata = fgets($fp, 4096);
			$rdata = preg_replace('/\s+/', ' ', $rdata);
	
			// debug
			echo_r($rdata);
	
			// required!!! otherwise timeout!
			if (server_ping($fp, $rdata))
				continue;
	
			// join and part
			if (channel_join($fp, $rdata))
				continue;
			if (channel_part($fp, $rdata))
				continue;
			if (channel_nick($fp, $rdata))
				continue;
			if (channel_who($fp, $rdata))
				continue;
	
			// some nice things
			if (ctcp_version($fp, $rdata))
				continue;
			if (ctcp_time($fp, $rdata))
				continue;
	
			// help system
			if (msg_help($fp, $rdata))
				continue;
	
			// channel messages
			if (channel_msg_rank($fp, $rdata))
				continue;
			if (channel_msg_level($fp, $rdata))
				continue;
			if (channel_msg_ship($fp, $rdata))
				continue;
			if (channel_msg_seen($fp, $rdata))
				continue;
	
			// private messages
			if (private_msg_login($fp, $rdata))
				continue;
			if (private_msg_weapon($fp, $rdata))
				continue;
		}
	
		fclose ($fp); // close socket
		echo_r('Fatal error: Socket closed');
	
	} else {
	
		echo_r('There was an error connecting to '.$address.'/'.$port);
		exit();
	
	}
}
?>
