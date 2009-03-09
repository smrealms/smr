<?

$template->assign('PageTopic','VIEWING REPORTED MESSAGES');
$db2 = new SmrMySqlDatabase();
$db->query('DELETE FROM message_notify WHERE from_id = 0');
$db->query('SELECT * FROM message_notify');
$container = array();
$container['url'] = 'notify_delete_processing.php';
if ($db->getNumRows()) {

    $PHP_OUTPUT.=create_echo_form($container);
    $PHP_OUTPUT.=('<br />');
    $PHP_OUTPUT.=('Click either name to reply<br />');
    $PHP_OUTPUT.=('<table width="100%" border="0" class="standard" cellspacing="0" cellpadding="1">');

    while($db->nextRecord()) {

		$PHP_OUTPUT.=('<tr>');
		$notify_id = $db->getField('notify_id');
		$PHP_OUTPUT.=('<td><input type="checkbox" name="notify_id[]" value="'.$notify_id.'"></td>');
		$sender =& SmrPlayer::getPlayer($db->getField('from_id'), $db->getField('game_id'));
		$receiver =& SmrPlayer::getPlayer($db->getField('to_id'), $db->getField('game_id'));
		$sender_acc =& SmrAccount::getAccount($db->getField('from_id'));
		$receiver_acc =& SmrAccount::getAccount($db->getField('to_id'));
		$container = array();
		$container['url'] = 'skeleton.php';
		$container['body'] = 'notify_reply.php';
		$container['offender'] = $sender->getAccountID();
		$container['offended'] = $receiver->getAccountID();
		$container['game_id'] = $db->getField('game_id');
		$PHP_OUTPUT.=('<td nowrap="nowrap">');
		
		$offender = 'From: '.$sender_acc->login.' ('.$sender_acc->account_id.')';
		if ($sender_acc->login != $sender->getPlayerName())
			$offender .= ' a.k.a '.$sender->getPlayerName();
		$PHP_OUTPUT.=create_link($container, $offender);
		$PHP_OUTPUT.=('</td><td nowrap="nowrap">');
		//To: $receiver_acc->login ($receiver_acc->account_id)');
		$offended = 'To: '.$receiver_acc->login.' ('.$receiver_acc->account_id.')';
		if ($receiver_acc->login != $receiver->getPlayerName())
			$offended .= ' a.k.a '.$receiver->getPlayerName();
		$PHP_OUTPUT.=create_link($container, $offended);
		$PHP_OUTPUT.=('</td><td>');
		$db2->query('SELECT * FROM game WHERE game_id = ' . $db->getField('game_id'));
		if ($db2->nextRecord()) $PHP_OUTPUT.=$db2->getField('game_name'); //$trader .= ' in ' . $db2->getField('game_name');
		else $PHP_OUTPUT.=('Game no longer exists'); //$trader .= ' in a game that no longer exists.';
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
		$PHP_OUTPUT.=($message);
		$PHP_OUTPUT.=('</td></tr>');

    }

    $PHP_OUTPUT.=('</table>');
    $PHP_OUTPUT.=create_submit('Delete');
    $PHP_OUTPUT.=('</form>');

} else
    $PHP_OUTPUT.=('There are no reported Messages.');

?>