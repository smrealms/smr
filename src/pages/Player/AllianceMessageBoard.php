<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use AbstractSmrPlayer;
use Globals;
use Menu;
use Smr\Database;
use Smr\Exceptions\PlayerNotFound;
use Smr\Page\PlayerPage;
use Smr\Page\ReusableTrait;
use Smr\Template;
use SmrAlliance;
use SmrPlayer;

class AllianceMessageBoard extends PlayerPage {

	use ReusableTrait;

	public string $file = 'alliance_message.php';

	public function __construct(
		private readonly int $allianceID,
		public ?string $preview = null,
		public ?string $topic = null,
		public ?bool $allianceEyesOnly = null
	) {}

	public function build(AbstractSmrPlayer $player, Template $template): void {
		$db = Database::getInstance();

		$allianceID = $this->allianceID;

		$alliance = SmrAlliance::getAlliance($allianceID, $player->getGameID());
		$template->assign('PageTopic', $alliance->getAllianceDisplayName(false, true));
		Menu::alliance($alliance->getAllianceID());

		$mbWrite = true;
		$in_alliance = true;
		if ($alliance->getAllianceID() != $player->getAllianceID()) {
			if (!in_array($player->getAccountID(), Globals::getHiddenPlayers())) {
				$in_alliance = false;
			}
			$dbResult = $db->read('SELECT 1 FROM alliance_treaties
						WHERE (alliance_id_1 = ' . $db->escapeNumber($alliance->getAllianceID()) . ' OR alliance_id_1 = ' . $db->escapeNumber($player->getAllianceID()) . ')
						AND (alliance_id_2 = ' . $db->escapeNumber($alliance->getAllianceID()) . ' OR alliance_id_2 = ' . $db->escapeNumber($player->getAllianceID()) . ')
						AND game_id = ' . $db->escapeNumber($player->getGameID()) . '
						AND mb_write = 1 AND official = \'TRUE\' LIMIT 1');
			$mbWrite = $dbResult->hasRecord();
		}
		$query = 'SELECT
			alliance_only, topic, thread_id,
			max(time) as sendtime,
			min(sender_id) as sender_id,
			count(reply_id) as num_replies
		FROM alliance_thread_topic
			JOIN alliance_thread USING(game_id,alliance_id,thread_id)
		WHERE game_id=' . $db->escapeNumber($alliance->getGameID()) . '
			AND alliance_id=' . $db->escapeNumber($alliance->getAllianceID());
		if (!$in_alliance) {
			$query .= ' AND alliance_only = ' . $db->escapeBoolean(false);
		}
		$query .= ' GROUP BY thread_id ORDER BY sendtime DESC';
		$dbResult = $db->read($query);
		$threads = [];
		if ($dbResult->hasRecord()) {

			$i = 0;
			$alliance_eyes = [];
			$thread_ids = [];
			$thread_topics = [];

			foreach ($dbResult->records() as $dbRecord) {
				$threadID = $dbRecord->getInt('thread_id');
				$alliance_eyes[$i] = $dbRecord->getBoolean('alliance_only');
				$threads[$i]['ThreadID'] = $threadID;

				$thread_ids[$i] = $threadID;
				$thread_topics[$i] = $dbRecord->getString('topic');

				$threads[$i]['Topic'] = $dbRecord->getString('topic');

				$dbResult2 = $db->read('SELECT time
							FROM player_read_thread
							WHERE ' . $player->getSQL() . '
							AND alliance_id =' . $db->escapeNumber($alliance->getAllianceID()) . '
							AND thread_id=' . $db->escapeNumber($threadID) . '
							AND time>' . $db->escapeNumber($dbRecord->getInt('sendtime')) . ' LIMIT 1');
				$threads[$i]['Unread'] = !$dbResult2->hasRecord();

				// Determine the thread author display name
				$sender_id = $dbRecord->getInt('sender_id');
				if ($sender_id == ACCOUNT_ID_PLANET) {
					$playerName = 'Planet Reporter';
				} elseif ($sender_id == ACCOUNT_ID_BANK_REPORTER) {
					$playerName = 'Bank Reporter';
				} elseif ($sender_id == ACCOUNT_ID_ADMIN) {
					$playerName = 'Game Admins';
				} else {
					try {
						$author = SmrPlayer::getPlayer($sender_id, $player->getGameID());
						$playerName = $author->getLinkedDisplayName(false);
					} catch (PlayerNotFound) {
						$playerName = 'Unknown'; // default
					}
				}
				$threads[$i]['Sender'] = $playerName;

				$dbResult2 = $db->read('SELECT * FROM player_has_alliance_role JOIN alliance_has_roles USING(game_id,alliance_id,role_id) WHERE ' . $player->getSQL() . ' AND alliance_id=' . $db->escapeNumber($alliance->getAllianceID()) . ' LIMIT 1');
				$threads[$i]['CanDelete'] = $player->getAccountID() == $sender_id || $dbResult2->record()->getBoolean('mb_messages');
				if ($threads[$i]['CanDelete']) {
					$container = new AllianceMessageBoardDeleteThreadProcessor($allianceID, $this, $threadID);
					$threads[$i]['DeleteHref'] = $container->href();
				}
				$threads[$i]['Replies'] = $dbRecord->getInt('num_replies');
				$threads[$i]['SendTime'] = $dbRecord->getInt('sendtime');
				++$i;
			}

			for ($j = 0; $j < $i; $j++) {
				$container = new AllianceMessageBoardView($this->allianceID, $thread_ids, $thread_topics, $alliance_eyes, $j);
				$threads[$j]['ViewHref'] = $container->href();
			}
		}
		$template->assign('Threads', $threads);

		if ($mbWrite || in_array($player->getAccountID(), Globals::getHiddenPlayers())) {
			$container = new AllianceMessageBoardAddProcessor($allianceID, $this);
			$template->assign('CreateNewThreadFormHref', $container->href());
			$template->assign('Preview', $this->preview);
			$template->assign('Topic', $this->topic);
			$template->assign('AllianceEyesOnly', $this->allianceEyesOnly);
		}
	}

}
