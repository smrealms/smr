<?php

/*
function notice_nickserv_registered_user($fp, $rdata)
{

    // :NickServ!services@coldfront.net NOTICE Caretaker
    if (preg_match('/^:NickServ!services@coldfront.net NOTICE Caretaker :([^ ]+) is ([^.]+)\s$/i', $rdata, $msg)) {

        $nick = $msg[1];
        $registered_nick = $msg[2];

        echo_r('[NOTICE_NICKSERV_REGISTERED_NICK] ' . $nick . ' is ' . $registered_nick);

        $db = new SmrMySqlDatabase();
        $db2 = new SmrMySqlDatabase();

        $db->query('SELECT * FROM irc_seen WHERE nick = ' . $db->escapeString($nick));
        while ($db->nextRecord()) {
            $seen_id = $db->getField('seen_id');

            $db2->query('UPDATE irc_seen SET ' .
                        'registered_nick = ' . $db->escapeString($registered_nick) . ' ' .
                        'WHERE seen_id = ' . $seen_id);
        }

        return true;

    }

    return false;

}

function notice_nickserv_unknown_user($fp, $rdata)
{

    // :NickServ!services@coldfront.net NOTICE Caretaker :Nickname Slevin isn't registered.
    if (preg_match('/^:NickServ!services@coldfront.net NOTICE Caretaker :Nickname .(.*). isn\'t registered\.\s$/i', $rdata, $msg)) {

        $nick = $msg[1];

        echo_r('[NOTICE_NICKSERV_UNKNOWN_NICK] ' . $nick);

        $db = new SmrMySqlDatabase();
        $db2 = new SmrMySqlDatabase();

        $db->query('SELECT * FROM irc_seen WHERE nick = ' . $db->escapeString($nick));
        while ($db->nextRecord()) {
            $seen_id = $db->getField('seen_id');

            $db2->query('UPDATE irc_seen SET ' .
                        'registered_nick = ' . $db->escapeString('_UNREGISTERED_') . ' ' .
                        'WHERE seen_id = ' . $seen_id);
        }

        return true;

    }

    return false;

}
*/

?>