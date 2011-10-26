<?php

function channel_msg_sms($fp, $rdata)
{

	if (preg_match('/^:(.*)!(.*)@(.*)\sPRIVMSG\s(.*)\s:!sms\s$/i', $rdata, $msg)) {

		$nick = $msg[1];
		$user = $msg[2];
		$host = $msg[3];
		$channel = $msg[4];

		echo_r('[SMS] by ' . $nick . ' in ' . $channel);

		fputs($fp, 'PRIVMSG ' . $channel . ' :The !sms command enables you to send text messages to the users cellphones.' . EOL);
		fputs($fp, 'PRIVMSG ' . $channel . ' :The following sub commands are available:' . EOL);
		fputs($fp, 'PRIVMSG ' . $channel . ' :  !op send <nick> <msg>           Sends the <msg> to the cell phone of the user identified by <nick>' . EOL);
		fputs($fp, 'PRIVMSG ' . $channel . ' :  !op search <partial nick>%      Searches the database for the nick and returns the nick found (% Wildcard)' . EOL);

		return true;

	}

	return false;

}

function channel_msg_sms_search($fp, $rdata, $account, $player)
{

	if (preg_match('/^:(.*)!(.*)@(.*)\sPRIVMSG\s(.*)\s:!sms search (.*)\s$/i', $rdata, $msg)) {

		$nick = $msg[1];
		$user = $msg[2];
		$host = $msg[3];
		$channel = $msg[4];
		$recv = trim($msg[5]);

		echo_r('[SMS_SEARCH] by ' . $nick . ' in ' . $channel . ' for ' . $recv);

		if (($blacklist_reason = $account->isSmsBlacklisted()) !== false) {
			fputs($fp, 'PRIVMSG ' . $channel . ' :' . $nick . ', you are not allowed to send text messages via Caretaker. Reason: ' . $blacklist_reason . EOL);
			return true;
		}

		// check if we know this user we try to send a text too
		$recv_account =& SmrAccount::findAccountByIrcNick($recv, true);
		if ($recv_account == null) {
			fputs($fp, 'PRIVMSG ' . $channel . ' :' . $nick . ', I don\'t know a player that goes by the nick \'' . $recv . '\'.' . EOL);
			return true;
		}

		// multiple results (we don't hint who and what)
		if ($recv_account === true) {
			fputs($fp, 'PRIVMSG ' . $channel . ' :' . $nick . ', your query matches more than one player.' . EOL);
			return true;
		}

		// no cell phone?
		if (strlen($recv_account->getCellPhone()) == 0) {
			fputs($fp, 'PRIVMSG ' . $channel . ' :' . $nick . ', ' . $recv_account->getIrcNick() . ' has not provided a cell phone number.' . EOL);
		} else {
			fputs($fp, 'PRIVMSG ' . $channel . ' :' . $nick . ', ' . $recv_account->getIrcNick() . ' has provided a cell phone number and can receive text messages.' . EOL);
		}

		return true;

	}

	return false;

}

function channel_msg_sms_send($fp, $rdata, $account, $player)
{

	if (preg_match('/^:(.*)!(.*)@(.*)\sPRIVMSG\s(.*)\s:!sms send ([^ ]+) (.*)\s$/i', $rdata, $msg)) {

		$nick = $msg[1];
		$user = $msg[2];
		$host = $msg[3];
		$channel = $msg[4];
		$recv = $msg[5];
		$msg = trim($msg[6]);

		echo_r('[SMS_SEND] by ' . $nick . ' in ' . $channel . ' for ' . $recv);

		if (($blacklist_reason = $account->isSmsBlacklisted()) !== false) {
			fputs($fp, 'PRIVMSG ' . $channel . ' :' . $nick . ', you are not allowed to send text messages via Caretaker. Reason: ' . $blacklist_reason . EOL);
			return true;
		}

		// check if we know this user we try to send a text too
		$recv_account =& SmrAccount::getAccountByIrcNick($recv, true);
		if ($recv_account == null) {
			fputs($fp, 'PRIVMSG ' . $channel . ' :' . $nick . ', I don\'t know a player that goes by the nick \'' . $recv . '\'.' . EOL);
			return true;
		}

		// do we have a cellphone number?
		if (strlen($recv_account->getCellPhone()) == 0) {
			fputs($fp, 'PRIVMSG ' . $channel . ' :' . $nick . ', ' . $recv_account->getIrcNick() . ' has not provided a cell phone number.' . EOL);
			return true;
		}

		// do we have a msg
		if (empty($msg)) {
			fputs($fp, 'PRIVMSG ' . $channel . ' :' . $nick . ', you don\'t mind me asking what do you want to send to ' . $recv_account->getIrcNick() . '?' . EOL);
			return true;
		}

		// message too long?
		if (strlen($msg) > 160) {
			fputs($fp, 'PRIVMSG ' . $channel . ' :' . $nick . ', the message you want to send contains more than 160 characters.' . EOL);
			return true;
		}

		// +--------------------------------------------+
		// | Copyright (c) 2007-2009 by MOBILANT.DE     |
		// +--------------------------------------------+

		$url = 'http://gw.mobilant.com';
		$request = '';
		$param['key'] = SMS_GATEWAY_KEY;
		$param['message'] = $msg;
		// numbers like +177 will be (for some reason) 'corrected' to a german number because it's a common area code here
		// therefor support asked me to use 00-1-77 instad of +1-77
		$param['to'] = '00' . substr($recv_account->getCellPhone(), 1);
//		$param['from'] = 'SMR';
		$param['route'] = 'direct';
		$param['debug'] = SMS_DEBUG;
		$param['message_id'] = '1';
		$param['dlr'] = '1';
		$param['response'] = '1';

		foreach ($param as $key => $val)
		{
			$request .= $key . '=' . urlencode($val);
			$request .= '&';
		}

		echo_r('Calling url: ' . $url . '?' . $request);

		// request url = send text
		$response = @file($url . '?' . $request);

		$response_code = intval($response[0]);
		$message_id = intval($response[1]);

		// insert log
		$db = new SmrMySqlDatabase();
		$db->query('INSERT INTO account_sms_log (account_id, time, receiver_id, receiver_cell, response_code, message_id) ' .
		           'VALUES (' . $account->getAccountID() . ', ' . time() . ', ' . $recv_account->getAccountID() . ', ' . $db->escapeString($recv_account->getCellPhone()) . ', ' . $response_code . ', ' . $message_id . ')');

		// confirm sending
		if (SMS_DEBUG) {
			fputs($fp, 'PRIVMSG ' . $channel . ' :' . $nick . ', sending SMS messages is currently disabled.' . EOL);
		} else {
			if ($response_code == 100)
				fputs($fp, 'PRIVMSG ' . $channel . ' :' . $nick . ', your text message will be delivered to ' . $recv_account->getIrcNick() . ' immediately.' . EOL);
			elseif ($response_code == 10)
				fputs($fp, 'PRIVMSG ' . $channel . ' :' . $nick . ', there was an error while sending your text message: Unknown receiver number!' . EOL);
			elseif ($response_code == 20)
				fputs($fp, 'PRIVMSG ' . $channel . ' :' . $nick . ', there was an error while sending your text message: Unknown sender number!' . EOL);
			elseif ($response_code == 30)
				fputs($fp, 'PRIVMSG ' . $channel . ' :' . $nick . ', there was an error while sending your text message: Error in message!' . EOL);
			elseif ($response_code == 40)
				fputs($fp, 'PRIVMSG ' . $channel . ' :' . $nick . ', there was an error while sending your text message: Unknown route!' . EOL);
			elseif ($response_code == 50)
				fputs($fp, 'PRIVMSG ' . $channel . ' :' . $nick . ', there was an error while sending your text message: Identifikation failed!' . EOL);
			elseif ($response_code == 60)
				fputs($fp, 'PRIVMSG ' . $channel . ' :' . $nick . ', there was an error while sending your text message: Insufficient funds! Please donate!' . EOL);
			elseif ($response_code == 70)
				fputs($fp, 'PRIVMSG ' . $channel . ' :' . $nick . ', there was an error while sending your text message: Text message can\'t be delivered!' . EOL);
			elseif ($response_code == 71)
				fputs($fp, 'PRIVMSG ' . $channel . ' :' . $nick . ', there was an error while sending your text message: Feature not possible!' . EOL);
			elseif ($response_code == 80)
				fputs($fp, 'PRIVMSG ' . $channel . ' :' . $nick . ', there was an error while sending your text message: Error while delivering to SMS-C!' . EOL);
			else
				fputs($fp, 'PRIVMSG ' . $channel . ' :' . $nick . ', there was an error while sending your text message' . EOL);
		}

		return true;

	}

	return false;

}

?>