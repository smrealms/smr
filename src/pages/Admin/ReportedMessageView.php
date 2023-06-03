<?php declare(strict_types=1);

namespace Smr\Pages\Admin;

use Smr\Account;
use Smr\Database;
use Smr\Game;
use Smr\Messages;
use Smr\Page\AccountPage;
use Smr\Player;
use Smr\Template;

class ReportedMessageView extends AccountPage {

	public string $file = 'admin/notify_view.php';

	public function build(Account $account, Template $template): void {
		$template->assign('PageTopic', 'Viewing Reported Messages');

		$container = new ReportedMessageDeleteProcessor();
		$template->assign('DeleteHREF', $container->href());

		$db = Database::getInstance();
		$dbResult = $db->read('SELECT * FROM message_notify');
		$messages = [];
		foreach ($dbResult->records() as $dbRecord) {
			$gameID = $dbRecord->getInt('game_id');
			$sender = Messages::getMessagePlayer($dbRecord->getInt('from_id'), $gameID);
			$receiver = Messages::getMessagePlayer($dbRecord->getInt('to_id'), $gameID);

			$container = new ReportedMessageReply(
				offenderAccountID: $dbRecord->getInt('from_id'),
				offendedAccountID: $dbRecord->getInt('to_id'),
				gameID: $gameID,
			);

			$getName = function(Player|string $messagePlayer) use ($container, $account): string {
				if ($messagePlayer instanceof Player) {
					$name = $messagePlayer->getDisplayName() . ' (Login: ' . $messagePlayer->getAccount()->getLogin() . ')';
				} else {
					$name = $messagePlayer;
				}
				// If we can send admin messages, make the names reply links
				if ($account->hasPermission(PERMISSION_SEND_ADMIN_MESSAGE)) {
					$name = create_link($container, $name);
				}
				return $name;
			};

			if (!Game::gameExists($gameID)) {
				$gameName = 'Game ' . $gameID . ' no longer exists';
			} else {
				$gameName = Game::getGame($gameID)->getDisplayName();
			}

			$messages[] = [
				'notifyID' => $dbRecord->getInt('notify_id'),
				'senderName' => $getName($sender),
				'receiverName' => $getName($receiver),
				'gameName' => $gameName,
				'sentDate' => date($account->getDateTimeFormat(), $dbRecord->getInt('sent_time')),
				'reportDate' => date($account->getDateTimeFormat(), $dbRecord->getInt('notify_time')),
				'text' => bbify($dbRecord->getString('text'), $gameID),
			];
		}
		$template->assign('Messages', $messages);
	}

}
