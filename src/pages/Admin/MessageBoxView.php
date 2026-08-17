<?php declare(strict_types=1);

namespace Smr\Pages\Admin;

use Smr\Account;
use Smr\Database;
use Smr\Game;
use Smr\Messages;
use Smr\Page\AccountPage;
use Smr\Page\ReusableTrait;
use Smr\Player;
use Smr\Template;

class MessageBoxView extends AccountPage {

	use ReusableTrait;
	public function __construct(
		private readonly ?int $boxTypeID = null,
	) {}

	public function build(Account $account, Template $template): void {
		$db = Database::getInstance();

		if ($this->boxTypeID === null) {
			$template->pageTopic = 'Viewing Message Boxes';

			$totalMessages = [];
			$dbResult = $db->read('SELECT count(message_id), box_type_id
						FROM message_boxes
						GROUP BY box_type_id');
			foreach ($dbResult->records() as $dbRecord) {
				$totalMessages[$dbRecord->getInt('box_type_id')] = $dbRecord->getInt('count(message_id)');
			}
			$boxes = [];
			foreach (Messages::getAdminBoxNames() as $boxTypeID => $boxName) {
				$container = new self($boxTypeID);
				$boxes[$boxTypeID] = [
					'ViewHREF' => $container->href(),
					'BoxName' => $boxName,
					'TotalMessages' => $totalMessages[$boxTypeID],
				];
			}
			$template->pageRenderer = fn() => MessageBoxViewRenderer::renderBoxes($boxes);
		} else {
			$boxName = Messages::getAdminBoxNames()[$this->boxTypeID];
			$template->pageTopic = 'Viewing ' . $boxName;

			$dbResult = $db->select(
				'message_boxes',
				['box_type_id' => $this->boxTypeID],
				orderBy: ['send_time'],
				order: ['DESC'],
			);
			$messages = [];
			if ($dbResult->hasRecord()) {
				foreach ($dbResult->records() as $dbRecord) {
					$gameID = $dbRecord->getInt('game_id');
					$validGame = $gameID > 0 && Game::gameExists($gameID);
					$messageID = $dbRecord->getInt('message_id');
					$messages[$messageID] = [
						'ID' => $messageID,
					];

					$senderID = $dbRecord->getInt('sender_id');
					if ($senderID === 0) {
						$senderName = 'User not logged in';
					} else {
						$senderAccount = Account::getAccount($senderID);
						$senderName = $senderAccount->getLogin() . ' (' . $senderID . ')';
						if ($validGame) {
							$senderPlayer = Player::getPlayer($senderID, $gameID);
							$senderName .= ' a.k.a ' . $senderPlayer->getDisplayName();
							if ($account->hasPermission(PERMISSION_SEND_ADMIN_MESSAGE)) {
								$container = new MessageBoxReply(
									boxTypeID: $this->boxTypeID,
									senderAccountID: $senderID,
									gameID: $gameID,
								);
								$messages[$messageID]['ReplyHREF'] = $container->href();
							}
						}
					}
					$messages[$messageID]['SenderName'] = $senderName;

					if ($gameID === 0) {
						$messages[$messageID]['GameName'] = 'No game selected';
					} elseif (!$validGame) {
						$messages[$messageID]['GameName'] = 'Game no longer exists';
					} else {
						$messages[$messageID]['GameName'] = Game::getGame($gameID)->getDisplayName();
					}

					$messages[$messageID]['SendTime'] = date($account->getDateTimeFormat(), $dbRecord->getInt('send_time'));
					$messages[$messageID]['Message'] = nl2br(htmlentities($dbRecord->getString('message_text')));
				}
			}
			$template->pageRenderer = fn() => MessageBoxViewRenderer::renderMessages(
				BackHREF: new self()->href(),
				DeleteHREF: new MessageBoxDeleteProcessor($this->boxTypeID)->href(),
				Messages: $messages,
			);
		}

	}

}
