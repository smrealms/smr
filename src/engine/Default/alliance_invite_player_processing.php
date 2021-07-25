<?php declare(strict_types=1);

$session = Smr\Session::getInstance();
$account = $session->getAccount();
$player = $session->getPlayer();

$receiverID = Request::getInt('account_id');
$addMessage = Request::get('message');
$expireDays = Request::getInt('expire_days');

$expires = Smr\Epoch::time() + 86400 * $expireDays;

// If sender is mail banned or blacklisted by receiver, omit the custom message
$db = Smr\Database::getInstance();
$dbResult = $db->read('SELECT 1 FROM message_blacklist
            WHERE account_id='.$db->escapeNumber($receiverID) . '
              AND blacklisted_id='.$db->escapeNumber($player->getAccountID()));
if ($dbResult->hasRecord() || $account->isMailBanned()) {
	$addMessage = '';
}

// Construct the mail to send to the receiver
$msg = 'You have been invited to join an alliance!
This invitation will remain open for ' . $expireDays . ' ' . pluralise('day', $expireDays) . ' or until you join another alliance.
If you are currently in an alliance, you will leave it if you accept this invitation.

[join_alliance=' . $player->getAllianceID() . ']
';
if (!empty($addMessage)) {
	$msg .= '<br />' . $addMessage;
}

$player->sendAllianceInvitation($receiverID, $msg, $expires);

$container = Page::create('skeleton.php', 'alliance_invite_player.php');
$container->go();
