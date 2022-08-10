<?php declare(strict_types=1);

use Smr\Database;
use Smr\Epoch;

		$template = Smr\Template::getInstance();
		$session = Smr\Session::getInstance();
		$player = $session->getPlayer();
		$alliance = $player->getAlliance();
		$game = $player->getGame();

		$template->assign('PageTopic', $alliance->getAllianceDisplayName(false, true));
		Menu::alliance($alliance->getAllianceID());

		// Get list of pending invitations
		$pendingInvites = [];
		foreach (SmrInvitation::getAll($player->getAllianceID(), $player->getGameID()) as $invite) {
			$container = Page::create('alliance_invite_cancel_processing.php');
			$container['invite'] = $invite;

			$invited = $invite->getReceiver();
			$pendingInvites[$invited->getAccountID()] = [
				'invited' => $invited->getDisplayName(true),
				'invited_by' => $invite->getSender()->getDisplayName(),
				'expires' => format_time($invite->getExpires() - Epoch::time(), true),
				'cancelHREF' => $container->href(),
			];
		}
		$template->assign('PendingInvites', $pendingInvites);

		// Get list of players eligible to join this alliance.
		// List those who joined the game most recently first.
		$invitePlayers = [];
		if ($alliance->getNumMembers() < $game->getAllianceMaxPlayers()) {
			$db = Database::getInstance();
			$dbResult = $db->read('SELECT * FROM player
			            WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . '
			              AND alliance_id != ' . $db->escapeNumber($alliance->getAllianceID()) . '
			              AND npc = ' . $db->escapeBoolean(false) . '
			            ORDER BY player_id DESC');
			foreach ($dbResult->records() as $dbRecord) {
				$invitePlayer = SmrPlayer::getPlayer($dbRecord->getInt('account_id'), $player->getGameID(), false, $dbRecord);
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
