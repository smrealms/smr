<?php

function check_sms_dlr($fp)
{
	// get one dlr per time so we do not spam anyone
	$db = new SmrMySqlDatabase();
	$db->query(
		'SELECT     * ' .
		'FROM       account_sms_dlr ' .
		'LEFT JOIN  account_sms_log USING (message_id) ' .
		'WHERE      announce = 0 ' .
		'ORDER BY   log_id'
	);
	if ($db->nextRecord()) {
		$message_id = $db->getField('message_id');
		$sender_id = $db->getField('account_id');
		$receiver_id = $db->getField('receiver_id');
		$status = $db->getField('status');
//		$send_time = $db->getField('send_time');
//		$receive_time = $db->getField('receive_time');

		echo_r('Found new SMS DLR... ' . $message_id);

		$sender =& SmrAccount::getAccount($sender_id, true);
		$receiver =& SmrAccount::getAccount($receiver_id, true);

		if ($status == 'DELIVERED') {
			fputs($fp, 'NOTICE ' . $sender->getIrcNick() . ' :Your text message has been delivered to ' . $receiver->getIrcNick() . '\'s cell phone.' . EOL);
		} elseif ($status == 'NOT_DELIVERED') {
			fputs($fp, 'NOTICE ' . $sender->getIrcNick() . ' :Your text message has NOT been delivered. Most likely ' . $receiver->getIrcNick() . ' has entered an invalid cell phone number.' . EOL);
		} elseif ($status == 'BUFFERED') {
			fputs($fp, 'NOTICE ' . $sender->getIrcNick() . ' :Your text message has been buffered and will be delivered when ' . $receiver->getIrcNick() . ' turns on his/her cell phone.' . EOL);
		} elseif ($status == 'TRANSMITTED') {
			fputs($fp, 'NOTICE ' . $sender->getIrcNick() . ' :Your text message has been sent.' . EOL);
		} else {
			fputs($fp, 'NOTICE ' . $sender->getIrcNick() . ' :Something unexpected happend to your text message. Status returned by gateway was: ' . $status . EOL);
		}

		// update announce status
		$db->query('UPDATE account_sms_dlr ' .
		           'SET    announce = 1 ' .
		           'WHERE  message_id = ' . $message_id .
                   'AND    status = ' . $status);
	}

}

function check_sms_response($fp)
{
	// get one dlr per time so we do not spam anyone
	$db = new SmrMySqlDatabase();
	$db->query(
		'SELECT     * ' .
		'FROM       account_sms_response ' .
		'LEFT JOIN  account_sms_log USING (message_id) ' .
		'WHERE      announce = 0'
	);
	if ($db->nextRecord()) {
		$response_id = $db->getField('response_id');
		$message_id = $db->getField('message_id');
		$message = $db->getField('message');
		$orig_sender_id = $db->getField('account_id');

		echo_r('Found new SMS response... ' . $message_id);

		$orig_sender =& SmrAccount::getAccount($orig_sender_id, true);

		fputs($fp, 'NOTICE ' . $orig_sender->getIrcNick() . ' :You have received a response to your text: ' . EOL);
		fputs($fp, 'NOTICE ' . $orig_sender->getIrcNick() . ' :' . $message . EOL);

		// update announce status
		$db->query('UPDATE account_sms_response ' .
		           'SET    announce = 1 ' .
		           'WHERE  response_id = ' . $response_id);
	}

}

function check_planet_builds()
{

}

function check_events($fp)
{
	global $events;

	foreach ($events as $key => $event) {

		if ($event[0] < time()) {
			echo_r('[TIMER] finished. Sending a note to ' . $event[2]);
			fputs($fp, 'NOTICE ' . $event[2] . ' :' . $event[1] . EOL);
			unset($events[$key]);
		}

	}
}

?>