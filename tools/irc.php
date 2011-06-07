#!/usr/bin/php -q
<?php

function echo_r($message)
{
	if(is_array($message))
	{
		foreach($message as $msg)
			echo_r($msg);
	}
	else
		echo date("d.m.Y H:i:s => ").$message.EOL;
}

// config file
include( realpath(dirname(__FILE__)) . '/../htdocs/config.inc');

include(LIB . '/Default/SmrMySqlDatabase.class.inc');

include(ENGINE . '/Default/smr.inc');

require_once(get_file_loc('smr_alliance.inc'));

$address = 'ice.coldfront.net';
$port = 6667;
$channel = '#smr';
$nick = 'Caretaker';
$pass = 'smr4ever';

$events = array();

echo_r('Connecting to '.$address);

// include all sub files
require_once('irc/server.php');
require_once('irc/ctcp.php');
require_once('irc/invite.php');
//require_once('irc/rank.php');
//require_once('irc/ship.php');
require_once('irc/user.php');
require_once('irc/query.php');
require_once('irc/notice.php');
//require_once('irc/weapon.php');
//require_once('irc/level.php');
require_once('irc/channel.php');
require_once('irc/channel_msg.php');
require_once('irc/maintenance.php');

// database object
$db = new SmrMySqlDatabase();

// delete all seen stats that appear to be on (we do not want to take something for granted that happend while we were away)
$db->query('DELETE from irc_seen WHERE signed_off = 0');

$fp = fsockopen($address, $port);
if ($fp)
{
    stream_set_blocking($fp, TRUE);
    echo_r('Socket '.$fp.' is connected... Identifying...');

    fputs($fp, 'NICK CareGhost'.EOL);
    fputs($fp, 'USER '.strtolower($nick).' oberon smrealms.de :Official SMR bot'.EOL);

    // kill any other user that is using our nick
    fputs($fp, 'NICKSERV GHOST '.$nick.' '.$pass.EOL);

    sleep(1);

    fputs($fp, 'NICK '.$nick.EOL);
    fputs($fp, 'NICKSERV IDENTIFY '.$pass.EOL);

    // join our public channel
    fputs($fp, 'JOIN '.$channel.EOL);
    sleep(1);
    fputs($fp, 'WHO '.$channel.EOL);

	// join any alliance channels
	$db->query('SELECT    channel ' .
	           'FROM      irc_alliance_has_channel ' .
	           'LEFT JOIN game USING (game_id) ' .
	           'WHERE     start_date < ' . time() .
	           '  AND     end_date > ' . time());
	while ($db->nextRecord()) {
		$alliance_channel = $db->getField('channel');

		// join channels
		fputs($fp, 'JOIN #'.$alliance_channel.EOL);
		sleep(1);
		fputs($fp, 'WHO #'.$alliance_channel.EOL);
	}

    while (!feof($fp))
    {

        $rdata = fgets($fp, 4096);
        $rdata = preg_replace('/\s+/', ' ', $rdata);

	    // we simply do some poll stuff here
	    check_planet_builds($fp);
	    check_events($fp);
        
        // required!!! otherwise timeout!
        if (server_ping($fp, $rdata))
            continue;

	    // server msg
        if (server_msg_307($fp, $rdata))
            continue;
        if (server_msg_318($fp, $rdata))
            continue;
        if (server_msg_352($fp, $rdata))
            continue;
        if (server_msg_401($fp, $rdata))
            continue;

        // some nice things
        if (ctcp_version($fp, $rdata))
            continue;
        if (ctcp_finger($fp, $rdata))
            continue;
        if (ctcp_time($fp, $rdata))
            continue;
        if (ctcp_ping($fp, $rdata))
            continue;

	    if (invite($fp, $rdata))
	        continue;

        // join and part
        if (channel_join($fp, $rdata))
            continue;
        if (channel_part($fp, $rdata))
            continue;

	    // nick change and quit
        if (user_nick($fp, $rdata))
            continue;
        if (user_quit($fp, $rdata))
            continue;

        // channel msg (!xyz)
        if (channel_msg_help($fp, $rdata))
            continue;
        if (channel_msg_seen($fp, $rdata))
            continue;
        if (channel_msg_seed($fp, $rdata))
            continue;
        if (channel_msg_seedlist($fp, $rdata))
            continue;
        if (channel_msg_seedlist_add($fp, $rdata))
            continue;
        if (channel_msg_seedlist_del($fp, $rdata))
            continue;
        if (channel_msg_op($fp, $rdata))
	        continue;
        if (channel_msg_op_info($fp, $rdata))
	        continue;
        if (channel_msg_op_cancel($fp, $rdata))
	        continue;
        if (channel_msg_op_set($fp, $rdata))
	        continue;
        if (channel_msg_op_signup($fp, $rdata))
	        continue;
        if (channel_msg_op_list($fp, $rdata))
	        continue;
        if (channel_msg_money($fp, $rdata))
	        continue;
        if (channel_msg_timer($fp, $rdata))
	        continue;
	    
/* doing these later...

        // channel messages
        if (channel_msg_rank($fp, $rdata))
            continue;
        if (channel_msg_level($fp, $rdata))
            continue;
        if (channel_msg_ship($fp, $rdata))
            continue;
*/

/* doing these later...

        // private messages
        if (private_msg_login($fp, $rdata))
            continue;
        if (private_msg_weapon($fp, $rdata))
            continue;

*/

	    // MrSpock can use this to send commands as caretaker
        if (query_command($fp, $rdata))
            continue;


        // debug
        if (strlen($rdata) > 0) {
//            echo_r('[UNKNOWN] '.$rdata);
            continue;
        }

    }

    fclose ($fp); // close socket
    echo_r('Fatal error: Socket closed');

} else {

    echo_r('There was an error connecting to '.$address.'/'.$port);
    exit();

}

function fill_string($str, $length) {

	while (strlen($str) < $length)
		$str .= ' ';

	return $str;

}

?>
