<?php declare(strict_types=1);

$receiverPlayerID = Request::getInt('player_id');
$addMessage = Request::get('message');
$expireDays = Request::getInt('expire_days');

$expires = TIME + 86400 * $expireDays;

// If sender is mail banned or blacklisted by receiver, omit the custom message
$db->query('SELECT 1 FROM message_blacklist
            WHERE player_id='.$db->escapeNumber($receiverPlayerID) . '
              AND blacklisted_player_id='.$db->escapeNumber($player->getPlayerID()));
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

$player->sendAllianceInvitation($receiverPlayerID, $msg, $expires);

$container = create_container('skeleton.php', 'alliance_invite_player.php');
forward($container);
