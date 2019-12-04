<?php declare(strict_types=1);

$receiverID = $_POST['account_id'];
$addMessage = $_POST['message'];
$expireDays = $_POST['expire_days'];

if (!is_numeric($expireDays)) {
	create_error('Expiration must be a number of days!');
}
$expires = TIME + 86400 * $expireDays;

// If sender is mail banned or blacklisted by receiver, omit the custom message
$db->query('SELECT 1 FROM message_blacklist
            WHERE account_id='.$db->escapeNumber($receiverID) . '
              AND blacklisted_id='.$db->escapeNumber($player->getAccountID()));
if ($db->nextRecord() || $account->isMailBanned()) {
	$addMessage = '';
}

// Construct the mail to send to the receiver
$msg = 'You have been invited to join an alliance!
This invitation will remain open for '.$expireDays . ' ' . pluralise('day', $expireDays) . ' or until you join another alliance.
If you are currently in an alliance, you will leave it if you accept this invitation.

[join_alliance='.$player->getAllianceID() . ']
';
if (!empty($addMessage)) {
	$msg .= '<br />' . $addMessage;
}

// Send mail to player
$player->sendMessage($receiverID, MSG_PLAYER, $msg, false, true, $expires, true);

// Record invitation in the database
$db->query('INSERT INTO alliance_invites_player (game_id, account_id, alliance_id, invited_by_id, expires) VALUES(' . $db->escapeNumber($player->getGameID()) . ', ' . $db->escapeNumber($receiverID) . ', ' . $db->escapeNumber($player->getAllianceID()) . ', ' . $db->escapeNumber($player->getAccountID()) . ', ' . $db->escapeNumber($expires) . ')');

$container = create_container('skeleton.php', 'alliance_invite_player.php');
forward($container);
