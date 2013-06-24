<?php
$template->assign('PageTopic','Viewing Reported Messages');

require_once(get_file_loc('message.functions.inc'));

$db->query('DELETE FROM message_notify WHERE from_id = 0');
$db->query('SELECT * FROM message_notify');
$container = array();
$container['url'] = 'notify_delete_processing.php';
if ($db->getNumRows()) {
	$PHP_OUTPUT.=create_echo_form($container);
	$PHP_OUTPUT.=('<br />');
	$PHP_OUTPUT.=('Click either name to reply<br />');
	$PHP_OUTPUT.=('<table width="100%" class="standard">');

	while($db->nextRecord()) {
		$PHP_OUTPUT.=('<tr>');
		$notify_id = $db->getField('notify_id');
		$PHP_OUTPUT.=('<td><input type="checkbox" name="notify_id[]" value="'.$notify_id.'"></td>');
		$gameID = $db->getField('game_id');
		$sender =& getMessagePlayer($db->getField('from_id'),$gameID);
		$receiver =& getMessagePlayer($db->getField('to_id'),$gameID);
		if(is_object($sender))
			$sender_acc = SmrAccount::getAccount($db->getField('from_id'));
		if(is_object($receiver))
			$receiver_acc = SmrAccount::getAccount($db->getField('to_id'));
	
		$container = create_container('skeleton.php','notify_reply.php');
		$container['offender'] = $db->getField('from_id');
		$container['offended'] = $db->getField('to_id');
		$container['game_id'] = $gameID;
		$PHP_OUTPUT.=('<td class="noWrap">');
		
		$offender = 'From: ';
		if(is_object($sender)) {
			$offender .= $sender_acc->getLogin().' ('.$sender_acc->getAccountID().')';
			if ($sender_acc->getLogin() != $sender->getPlayerName())
				$offender .= ' a.k.a '.$sender->getPlayerName();
		}
		else
			$offender .= $sender;
		$PHP_OUTPUT.=create_link($container, $offender);
		$PHP_OUTPUT.=('</td><td class="noWrap">');
		//To: $receiver_acc->getLogin() ($receiver_acc->getAccountID())');
		$offended = 'To: ';
		if(is_object($receiver)) {
			$offended .= $receiver_acc->getLogin().' ('.$receiver_acc->getAccountID().')';
			if ($receiver_acc->getLogin() != $receiver->getPlayerName())
				$offended .= ' a.k.a '.$receiver->getPlayerName();
		}
		else
			$offended .= $receiver;
		$PHP_OUTPUT.=create_link($container, $offended);
		$PHP_OUTPUT.=('</td><td>');
		if (!Globals::isValidGame($gameID)) $PHP_OUTPUT.=('Game no longer exists');
		else $PHP_OUTPUT.=Globals::getGameName($gameID);
		$PHP_OUTPUT.=('</td></tr><tr><td colspan="2">');
		$PHP_OUTPUT.=('Sent at ' . date(DATE_FULL_SHORT, $db->getField('sent_time')));
		$PHP_OUTPUT.=('</td><td colspan="2">');
		$PHP_OUTPUT.=('Notified at ' . date(DATE_FULL_SHORT, $db->getField('notify_time')));
		//$PHP_OUTPUT.=create_link($container, $trader);
		
		$PHP_OUTPUT.=('</td>');
		$PHP_OUTPUT.=('</tr>');
		$PHP_OUTPUT.=('<tr>');
		$PHP_OUTPUT.=('<td width="100%" colspan="4">');
		$message = $db->getField('text');
		$PHP_OUTPUT.=bbifyMessage($message);
		$PHP_OUTPUT.=('</td></tr>');

	}

	$PHP_OUTPUT.=('</table>');
	$PHP_OUTPUT.=create_submit('Delete');
	$PHP_OUTPUT.=('</form>');

} else
	$PHP_OUTPUT.=('There are no reported Messages.');

?>