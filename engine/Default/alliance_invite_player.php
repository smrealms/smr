<?php

$alliance = $player->getAlliance();
$game = $player->getGame();

$template->assign('PageTopic', $alliance->getAllianceName(false, true));
require_once(get_file_loc('menu.inc'));
create_alliance_menu($alliance->getAllianceID(), $alliance->getLeaderID());

// Remove any expired invitations
$db->query('DELETE FROM alliance_invites_player WHERE expires < ' . $db->escapeNumber(TIME));

// Get list of pending invitations
$pendingInvites = array();
$db->query('SELECT * FROM alliance_invites_player
            WHERE game_id = '.$db->escapeNumber($player->getGameID()).'
              AND alliance_id = '.$db->escapeNumber($alliance->getAllianceID()));
while ($db->nextRecord()) {
	$invited = SmrPlayer::getPlayer($db->getInt('account_id'), $player->getGameID());
	$invitedBy = SmrPlayer::getPlayer($db->getInt('invited_by_id'), $player->getGameID());
	$pendingInvites[$invited->getAccountID()] = array(
		'invited' => $invited->getDisplayName(true),
		'invited_by' => $invitedBy->getDisplayName(),
		'expires' => format_time($db->getInt('expires') - TIME, true),
	);
}
$template->assign('PendingInvites', $pendingInvites);

// Get list of players eligible to join this alliance.
// List those who joined the game most recently first.
$invitePlayers = array();
if ($alliance->getNumMembers() < $game->getAllianceMaxPlayers()) {
	$db->query('SELECT account_id FROM player
	            WHERE game_id = '.$db->escapeNumber($player->getGameID()).'
	              AND alliance_id != '.$db->escapeNumber($alliance->getAllianceID()).'
	              AND npc = '.$db->escapeBoolean(false).'
	            ORDER BY player_id DESC');
	while ($db->nextRecord()) {
		$invitePlayer = SmrPlayer::getPlayer($db->getInt('account_id'), $player->getGameID());
		if (in_array($invitePlayer->getAccountID(), array_keys($pendingInvites))) {
			// Don't display players we've already invited
			continue;
		}
		if ($alliance->getNumVeterans() < $game->getAllianceMaxVets() || !$invitePlayer->isVeteran()) {
			$invitePlayers[] = $invitePlayer;
		}
	}
}
$template->assign('InvitePlayers', $invitePlayers);

$template->assign('ThisGame', $game);
$template->assign('ThisAlliance', $alliance);
$template->assign('InviteHREF', SmrSession::getNewHREF(create_container('alliance_invite_player_processing.php')));
