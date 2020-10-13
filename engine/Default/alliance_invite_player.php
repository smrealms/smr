<?php declare(strict_types=1);

$alliance = $player->getAlliance();
$game = $player->getGame();

$template->assign('PageTopic', $alliance->getAllianceDisplayName(false, true));
Menu::alliance($alliance->getAllianceID(), $alliance->getLeaderID());

// Get list of pending invitations
$pendingInvites = array();
foreach (SmrInvitation::getAll($player->getAllianceID(), $player->getGameID()) as $invite) {
	$container = create_container('alliance_invite_cancel_processing.php');
	$container['invite'] = $invite;

	$invited = $invite->getReceiver();
	$pendingInvites[$invited->getPlayerID()] = array(
		'invited' => $invited->getDisplayName(true),
		'invited_by' => $invite->getSender()->getDisplayName(),
		'expires' => format_time($invite->getExpires() - TIME, true),
		'cancelHREF' => SmrSession::getNewHREF($container),
	);
}
$template->assign('PendingInvites', $pendingInvites);

// Get list of players eligible to join this alliance.
// List those who joined the game most recently first.
$invitePlayers = array();
if ($alliance->getNumMembers() < $game->getAllianceMaxPlayers()) {
	$db->query('SELECT * FROM player
	            WHERE game_id = '.$db->escapeNumber($player->getGameID()) . '
	              AND alliance_id != '.$db->escapeNumber($alliance->getAllianceID()) . '
	              AND npc = '.$db->escapeBoolean(false) . '
	            ORDER BY player_id DESC');
	while ($db->nextRecord()) {
		$invitePlayer = SmrPlayer::getPlayer($db->getInt('player_id'), $player->getGameID(), false, $db);
		if (array_key_exists($invitePlayer->getPlayerID(), $pendingInvites)) {
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
$template->assign('InviteHREF', SmrSession::getNewHREF(create_container('alliance_invite_player_processing.php')));
