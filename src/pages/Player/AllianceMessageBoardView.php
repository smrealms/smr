<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use AbstractSmrPlayer;
use Globals;
use Menu;
use Smr\Database;
use Smr\Epoch;
use Smr\Page\PlayerPage;
use Smr\Page\ReusableTrait;
use Smr\Template;
use SmrAlliance;
use SmrPlayer;

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

	public function build(AbstractSmrPlayer $player, Template $template): void {
		$allianceID = $this->allianceID;

		$alliance = SmrAlliance::getAlliance($allianceID, $player->getGameID());
		$thread_index = $this->threadIndex;
		$thread_id = $this->threadIDs[$thread_index];

		$template->assign('PageTopic', $this->threadTopics[$thread_index]);
		Menu::alliance($alliance->getAllianceID());

		$db = Database::getInstance();
		$db->replace('player_read_thread', [
			'account_id' => $db->escapeNumber($player->getAccountID()),
			'game_id' => $db->escapeNumber($player->getGameID()),
			'alliance_id' => $db->escapeNumber($alliance->getAllianceID()),
			'thread_id' => $db->escapeNumber($thread_id),
			'time' => $db->escapeNumber(Epoch::time() + 2),
		]);

		$mbWrite = true;
		if ($alliance->getAllianceID() != $player->getAllianceID()) {
			$dbResult = $db->read('SELECT 1 FROM alliance_treaties
							WHERE (alliance_id_1 = ' . $db->escapeNumber($alliance->getAllianceID()) . ' OR alliance_id_1 = ' . $db->escapeNumber($player->getAllianceID()) . ')' .
							' AND (alliance_id_2 = ' . $db->escapeNumber($alliance->getAllianceID()) . ' OR alliance_id_2 = ' . $db->escapeNumber($player->getAllianceID()) . ')' .
							' AND game_id = ' . $db->escapeNumber($player->getGameID()) .
							' AND mb_write = 1 AND official = \'TRUE\'');
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
					WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . '
						AND alliance_thread.alliance_id = ' . $db->escapeNumber($alliance->getAllianceID()) . ' AND alliance_thread.thread_id = ' . $db->escapeNumber($thread_id));
		foreach ($dbResult->records() as $dbRecord) {
			$accountID = $dbRecord->getInt('account_id');
			$players[$accountID] = SmrPlayer::getPlayer($accountID, $player->getGameID(), false, $dbRecord)->getLinkedDisplayName(false);
		}

		$dbResult = $db->read('SELECT mb_messages FROM player_has_alliance_role JOIN alliance_has_roles USING(game_id,alliance_id,role_id) WHERE ' . $player->getSQL() . ' AND alliance_id=' . $db->escapeNumber($alliance->getAllianceID()) . ' LIMIT 1');
		$thread['CanDelete'] = $dbResult->record()->getBoolean('mb_messages');

		$dbResult = $db->read('SELECT text, sender_id, time, reply_id
		FROM alliance_thread
		WHERE game_id=' . $db->escapeNumber($player->getGameID()) . '
		AND alliance_id=' . $db->escapeNumber($alliance->getAllianceID()) . '
		AND thread_id=' . $db->escapeNumber($thread_id) . '
		ORDER BY reply_id');

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
