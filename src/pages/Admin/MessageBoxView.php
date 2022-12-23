<?php declare(strict_types=1);

namespace Smr\Pages\Admin;

use Smr\Database;
use Smr\Messages;
use Smr\Page\AccountPage;
use Smr\Page\ReusableTrait;
use Smr\Template;
use SmrAccount;
use SmrGame;
use SmrPlayer;

class MessageBoxView extends AccountPage {

	use ReusableTrait;

	public string $file = 'admin/box_view.php';

	public function __construct(
		private readonly ?int $boxTypeID = null
	) {}

	public function build(SmrAccount $account, Template $template): void {
		$db = Database::getInstance();

		if ($this->boxTypeID === null) {
			$template->assign('PageTopic', 'Viewing Message Boxes');

			$boxes = [];
			foreach (Messages::getAdminBoxNames() as $boxTypeID => $boxName) {
				$container = new self($boxTypeID);
				$boxes[$boxTypeID] = [
					'ViewHREF' => $container->href(),
					'BoxName' => $boxName,
					'TotalMessages' => 0,
				];
			}
			$dbResult = $db->read('SELECT count(message_id), box_type_id
						FROM message_boxes
						GROUP BY box_type_id');
			foreach ($dbResult->records() as $dbRecord) {
				$boxes[$dbRecord->getInt('box_type_id')]['TotalMessages'] = $dbRecord->getInt('count(message_id)');
			}
			$template->assign('Boxes', $boxes);
		} else {
			$boxName = Messages::getAdminBoxNames()[$this->boxTypeID];
			$template->assign('PageTopic', 'Viewing ' . $boxName);

			$template->assign('BackHREF', (new self())->href());
			$dbResult = $db->read('SELECT * FROM message_boxes WHERE box_type_id=' . $db->escapeNumber($this->boxTypeID) . ' ORDER BY send_time DESC');
			$messages = [];
			if ($dbResult->hasRecord()) {
				$container = new MessageBoxDeleteProcessor($this->boxTypeID);
				$template->assign('DeleteHREF', $container->href());
				foreach ($dbResult->records() as $dbRecord) {
					$gameID = $dbRecord->getInt('game_id');
					$validGame = $gameID > 0 && SmrGame::gameExists($gameID);
					$messageID = $dbRecord->getInt('message_id');
					$messages[$messageID] = [
						'ID' => $messageID,
					];

					$senderID = $dbRecord->getInt('sender_id');
					if ($senderID == 0) {
						$senderName = 'User not logged in';
					} else {
						$senderAccount = SmrAccount::getAccount($senderID);
						$senderName = $senderAccount->getLogin() . ' (' . $senderID . ')';
						if ($validGame) {
							$senderPlayer = SmrPlayer::getPlayer($senderID, $gameID);
							$senderName .= ' a.k.a ' . $senderPlayer->getDisplayName();
							if ($account->hasPermission(PERMISSION_SEND_ADMIN_MESSAGE)) {
								$container = new MessageBoxReply(
									boxTypeID: $this->boxTypeID,
									senderAccountID: $senderID,
									gameID: $gameID
								);
								$messages[$messageID]['ReplyHREF'] = $container->href();
							}
						}
					}
					$messages[$messageID]['SenderName'] = $senderName;

					if ($gameID == 0) {
						$messages[$messageID]['GameName'] = 'No game selected';
					} elseif (!$validGame) {
						$messages[$messageID]['GameName'] = 'Game no longer exists';
					} else {
						$messages[$messageID]['GameName'] = SmrGame::getGame($gameID)->getDisplayName();
					}

					$messages[$messageID]['SendTime'] = date($account->getDateTimeFormat(), $dbRecord->getInt('send_time'));
					$messages[$messageID]['Message'] = nl2br(htmlentities($dbRecord->getString('message_text')));
				}
				$template->assign('Messages', $messages);
			}
		}
	}

}
