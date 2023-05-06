<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use Smr\AbstractPlayer;
use Smr\Alliance;
use Smr\Database;
use Smr\Exceptions\PlayerNotFound;
use Smr\Menu;
use Smr\Page\PlayerPage;
use Smr\Page\ReusableTrait;
use Smr\Player;
use Smr\Template;

class AllianceMessageBoard extends PlayerPage {

	use ReusableTrait;

	public string $file = 'alliance_message.php';

	public function __construct(
		private readonly int $allianceID,
		public ?string $preview = null,
		public ?string $topic = null,
		public ?bool $allianceEyesOnly = null
	) {}

	public function build(AbstractPlayer $player, Template $template): void {
		$db = Database::getInstance();

		$allianceID = $this->allianceID;

		$alliance = Alliance::getAlliance($allianceID, $player->getGameID());
		$template->assign('PageTopic', $alliance->getAllianceDisplayName(false, true));
		Menu::alliance($alliance->getAllianceID());

		$mbWrite = true;
		$in_alliance = true;
		if ($alliance->getAllianceID() !== $player->getAllianceID()) {
			if (!$player->isObserver()) {
				$in_alliance = false;
			}
			$dbResult = $db->read('SELECT 1 FROM alliance_treaties
						WHERE (alliance_id_1 = :alliance_id OR alliance_id_1 = :player_alliance_id)
						AND (alliance_id_2 = :alliance_id OR alliance_id_2 = :player_alliance_id)
						AND game_id = :game_id
						AND mb_write = 1 AND official = \'TRUE\' LIMIT 1', [
				'alliance_id' => $db->escapeNumber($alliance->getAllianceID()),
				'player_alliance_id' => $db->escapeNumber($player->getAllianceID()),
				'game_id' => $db->escapeNumber($player->getGameID()),
			]);
			$mbWrite = $dbResult->hasRecord();
		}

		// Get a list of all topics, with author, number of replies, and time of
		// the latest reply. (Note: this is a bit complicated because we don't
		// store the topic author in `alliance_thread_topic`.)
		$dbResult = $db->read('
			WITH
				t1 AS (
					SELECT * FROM alliance_thread_topic
						JOIN alliance_thread USING(game_id, alliance_id, thread_id)
					WHERE game_id = :game_id
						AND alliance_id = :alliance_id
						AND (
							:in_alliance = \'TRUE\'
							OR alliance_only = \'FALSE\'
						)
				),
				t2 AS (
					SELECT t1.*,
						FIRST_VALUE(sender_id) OVER (PARTITION BY thread_id ORDER BY reply_id) as author_account_id
					FROM t1
				)
			SELECT alliance_only, topic, thread_id, MAX(time) as sendtime, COUNT(reply_id) as num_replies, author_account_id
			FROM t2
			GROUP BY thread_id ORDER BY sendtime DESC
		', [
			'in_alliance' => $db->escapeBoolean($in_alliance),
			'game_id' => $db->escapeNumber($alliance->getGameID()),
			'alliance_id' => $db->escapeNumber($alliance->getAllianceID()),
		]);
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
							WHERE ' . AbstractPlayer::SQL . '
							AND alliance_id = :alliance_id
							AND thread_id = :thread_id
							AND time > :sendtime LIMIT 1', [
					...$player->SQLID,
					'alliance_id' => $db->escapeNumber($alliance->getAllianceID()),
					'thread_id' => $db->escapeNumber($threadID),
					'sendtime' => $db->escapeNumber($dbRecord->getInt('sendtime')),
				]);
				$threads[$i]['Unread'] = !$dbResult2->hasRecord();

				// Determine the thread author display name
				$authorAccountID = $dbRecord->getInt('author_account_id');
				if ($authorAccountID === ACCOUNT_ID_PLANET) {
					$playerName = 'Planet Reporter';
				} elseif ($authorAccountID === ACCOUNT_ID_BANK_REPORTER) {
					$playerName = 'Bank Reporter';
				} elseif ($authorAccountID === ACCOUNT_ID_ADMIN) {
					$playerName = 'Game Admins';
				} else {
					try {
						$author = Player::getPlayer($authorAccountID, $player->getGameID());
						$playerName = $author->getLinkedDisplayName(false);
					} catch (PlayerNotFound) {
						$playerName = 'Unknown'; // default
					}
				}
				$threads[$i]['Sender'] = $playerName;

				$dbResult2 = $db->read('SELECT * FROM player_has_alliance_role JOIN alliance_has_roles USING(game_id,alliance_id,role_id) WHERE ' . AbstractPlayer::SQL . ' AND alliance_id = :alliance_id LIMIT 1', [
					...$player->SQLID,
					'alliance_id' => $db->escapeNumber($alliance->getAllianceID()),
				]);
				$canDelete = $player->getAccountID() === $authorAccountID || $dbResult2->record()->getBoolean('mb_messages');
				if ($canDelete) {
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

		if ($mbWrite || $player->isObserver()) {
			$container = new AllianceMessageBoardAddProcessor($allianceID, $this);
			$template->assign('CreateNewThreadFormHref', $container->href());
			$template->assign('Preview', $this->preview);
			$template->assign('Topic', $this->topic);
			$template->assign('AllianceEyesOnly', $this->allianceEyesOnly);
		}
	}

}
