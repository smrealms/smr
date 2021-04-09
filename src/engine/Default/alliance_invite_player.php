<?php declare(strict_types=1);

$session = Smr\Session::getInstance();
$player = $session->getPlayer();
$alliance = $player->getAlliance();
$game = $player->getGame();

$template->assign('PageTopic', $alliance->getAllianceDisplayName(false, true));
Menu::alliance($alliance->getAllianceID());

// Get list of pending invitations
$pendingInvites = array();
foreach (SmrInvitation::getAll($player->getAllianceID(), $player->getGameID()) as $invite) {
	$container = Page::create('alliance_invite_cancel_processing.php');
	$container['invite'] = $invite;

	$invited = $invite->getReceiver();
	$pendingInvites[$invited->getAccountID()] = array(
		'invited' => $invited->getDisplayName(true),
		'invited_by' => $invite->getSender()->getDisplayName(),
		'expires' => format_time($invite->getExpires() - Smr\Epoch::time(), true),
		'cancelHREF' => $container->href(),
	);
}
$template->assign('PendingInvites', $pendingInvites);

// Get list of players eligible to join this alliance.
// List those who joined the game most recently first.
$invitePlayers = array();
if ($alliance->getNumMembers() < $game->getAllianceMaxPlayers()) {
	$db->query('SELECT account_id FROM player
	            WHERE game_id = '.$db->escapeNumber($player->getGameID()) . '
	              AND alliance_id != '.$db->escapeNumber($alliance->getAllianceID()) . '
	              AND npc = '.$db->escapeBoolean(false) . '
	            ORDER BY player_id DESC');
	while ($db->nextRecord()) {
		$invitePlayer = SmrPlayer::getPlayer($db->getInt('account_id'), $player->getGameID());
		if (array_key_exists($invitePlayer->getAccountID(), $pendingInvites)) {
			// Don't display players we've already invited
			continue;
		}
		if ($alliance->getNumVeterans() < $game->getAllianceMaxVets() || $invitePlayer->hasNewbieStatus()) {
			$invitePlayers[] = $invitePlayer;
		}
	}
}
$template->assign('InvitePlayers', $invitePlayers);

$template->assign('ThisGame', $game);
$template->assign('ThisAlliance', $alliance);
$template->assign('InviteHREF', Page::create('alliance_invite_player_processing.php')->href());
