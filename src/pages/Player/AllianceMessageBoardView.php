<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use Smr\AbstractPlayer;
use Smr\Alliance;
use Smr\Database;
use Smr\Epoch;
use Smr\Globals;
use Smr\Menu;
use Smr\Page\PlayerPage;
use Smr\Page\ReusableTrait;
use Smr\Player;
use Smr\Template;

class AllianceMessageBoardView extends PlayerPage {

	use ReusableTrait;

	public string $file = 'alliance_message_view.php';

	/**
	 * @param array<int> $threadIDs
	 * @param array<int, string> $threadTopics
	 * @param array<int, bool> $allianceEyesOnly
	 */
	public function __construct(
		private readonly int $allianceID,
		private readonly array $threadIDs,
		private readonly array $threadTopics,
		private readonly array $allianceEyesOnly,
		private int $threadIndex,
		public ?string $preview = null,
	) {}

	public function build(AbstractPlayer $player, Template $template): void {
		$allianceID = $this->allianceID;

		$alliance = Alliance::getAlliance($allianceID, $player->getGameID());
		$thread_index = $this->threadIndex;
		$thread_id = $this->threadIDs[$thread_index];

		$template->assign('PageTopic', $this->threadTopics[$thread_index]);
		Menu::alliance($alliance->getAllianceID());

		$db = Database::getInstance();
		$db->replace('player_read_thread', [
			...$player->SQLID,
			'alliance_id' => $alliance->getAllianceID(),
			'thread_id' => $thread_id,
			'time' => Epoch::time() + 2,
		]);

		$mbWrite = true;
		if ($alliance->getAllianceID() !== $player->getAllianceID()) {
			$dbResult = $db->read('SELECT 1 FROM alliance_treaties
							WHERE (alliance_id_1 = :alliance_id OR alliance_id_1 = :player_alliance_id)
							AND (alliance_id_2 = :alliance_id OR alliance_id_2 = :player_alliance_id)
							AND game_id = :game_id
							AND mb_write = 1 AND official = \'TRUE\'', [
				'alliance_id' => $db->escapeNumber($alliance->getAllianceID()),
				'player_alliance_id' => $db->escapeNumber($player->getAllianceID()),
				'game_id' => $db->escapeNumber($player->getGameID()),
			]);
			$mbWrite = $dbResult->hasRecord();
		}

		if (isset($this->threadIDs[$thread_index - 1])) {
			$container = clone($this);
			$container->threadIndex -= 1;
			$template->assign('PrevThread', ['Topic' => $this->threadTopics[$thread_index - 1], 'Href' => $container->href()]);
		}
		if (isset($this->threadIDs[$thread_index + 1])) {
			$container = clone($this);
			$container->threadIndex += 1;
			$template->assign('NextThread', ['Topic' => $this->threadTopics[$thread_index + 1], 'Href' => $container->href()]);
		}

		$thread = [];
		$thread['AllianceEyesOnly'] = $this->allianceEyesOnly[$thread_index];
		//for report type (system sent) messages
		$players = [
			ACCOUNT_ID_PLANET => 'Planet Reporter',
			ACCOUNT_ID_BANK_REPORTER => 'Bank Reporter',
		];
		$dbResult = $db->read('SELECT player.*
					FROM player
					JOIN alliance_thread USING (game_id)
					WHERE game_id = :game_id
						AND alliance_thread.alliance_id = :alliance_id AND alliance_thread.thread_id = :thread_id', [
			...$alliance->SQLID,
			'thread_id' => $db->escapeNumber($thread_id),
		]);
		foreach ($dbResult->records() as $dbRecord) {
			$accountID = $dbRecord->getInt('account_id');
			$players[$accountID] = Player::getPlayer($accountID, $player->getGameID(), false, $dbRecord)->getLinkedDisplayName(false);
		}

		$dbResult = $db->read('SELECT mb_messages FROM player_has_alliance_role JOIN alliance_has_roles USING(game_id,alliance_id,role_id) WHERE ' . AbstractPlayer::SQL . ' AND alliance_id = :alliance_id LIMIT 1', [
			...$player->SQLID,
			'alliance_id' => $db->escapeNumber($alliance->getAllianceID()),
		]);
		$thread['CanDelete'] = $dbResult->record()->getBoolean('mb_messages');

		$dbResult = $db->read('SELECT text, sender_id, time, reply_id
		FROM alliance_thread
		WHERE game_id = :game_id
		AND alliance_id = :alliance_id
		AND thread_id = :thread_id
		ORDER BY reply_id', [
			...$alliance->SQLID,
			'thread_id' => $db->escapeNumber($thread_id),
		]);

		$thread['CanDelete'] = $dbResult->getNumRecords() > 1 && $thread['CanDelete'];
		$thread['Replies'] = [];
		foreach ($dbResult->records() as $dbRecord) {
			$replyID = $dbRecord->getInt('reply_id');
			$thread['Replies'][$replyID] = [
				'Sender' => $players[$dbRecord->getInt('sender_id')],
				'Message' => $dbRecord->getString('text'),
				'SendTime' => $dbRecord->getInt('time'),
			];
			if ($thread['CanDelete']) {
				$container = new AllianceMessageBoardDeleteReplyProcessor($allianceID, $this, $thread_id, $replyID);
				$thread['Replies'][$replyID]['DeleteHref'] = $container->href();
			}
		}

		if ($mbWrite || in_array($player->getAccountID(), Globals::getHiddenPlayers())) {
			$container = new AllianceMessageBoardAddProcessor($allianceID, $this, $thread_id);
			$thread['CreateThreadReplyFormHref'] = $container->href();
		}
		$template->assign('Thread', $thread);
		$template->assign('Preview', $this->preview);
	}

}
