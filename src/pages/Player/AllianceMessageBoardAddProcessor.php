<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use AbstractSmrPlayer;
use Smr\Database;
use Smr\Epoch;
use Smr\Page\PlayerPageProcessor;
use Smr\Page\ReusableTrait;
use Smr\Request;

class AllianceMessageBoardAddProcessor extends PlayerPageProcessor {

	use ReusableTrait;

	public function __construct(
		private readonly int $allianceID,
		private readonly AllianceMessageBoard|AllianceMessageBoardView $lastPage,
		private readonly ?int $threadID = null
	) {}

	public function build(AbstractSmrPlayer $player): never {
		$db = Database::getInstance();

		$body = htmlentities(Request::get('body'), ENT_COMPAT, 'utf-8');
		$topic = Request::get('topic', ''); // only present for Create Thread
		$allEyesOnly = Request::has('allEyesOnly'); // only present for Create Thread

		$alliance_id = $this->allianceID;

		$action = Request::get('action');
		if ($action == 'Preview Thread' || $action == 'Preview Reply') {
			$container = $this->lastPage;
			$container->preview = $body;
			if ($container instanceof AllianceMessageBoard) {
				$container->topic = $topic;
				$container->allianceEyesOnly = $allEyesOnly;
			}
			$container->go();
		}

		// it could be we got kicked during writing the msg
		if (!$player->hasAlliance()) {
			create_error('You are not in an alliance!');
		}

		if (empty($body)) {
			create_error('You must enter text!');
		}

		// if we don't have a thread id
		if ($this->threadID === null) {
			// get one
			$dbResult = $db->read('SELECT IFNULL(max(thread_id)+1, 0) AS next_thread_id FROM alliance_thread
						WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . '
						AND alliance_id = ' . $db->escapeNumber($alliance_id));
			$thread_id = $dbResult->record()->getInt('next_thread_id');
		} else {
			$thread_id = $this->threadID;
		}

		// now get the next reply id
		$dbResult = $db->read('SELECT IFNULL(max(reply_id)+1, 0) AS next_reply_id FROM alliance_thread
					WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . '
					AND alliance_id = ' . $db->escapeNumber($alliance_id) . '
					AND thread_id = ' . $db->escapeNumber($thread_id));
		$reply_id = $dbResult->record()->getInt('next_reply_id');

		// only add the topic if it's the first reply
		if ($reply_id == 0) {
			if (empty($topic)) {
				create_error('You must enter a topic!');
			}

			if (strlen($topic) > 255) {
				create_error('Topic can\'t be longer than 255 chars!');
			}

			// test if this topic already exists
			$dbResult = $db->read('SELECT 1 FROM alliance_thread_topic
						WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . '
						AND alliance_id = ' . $db->escapeNumber($alliance_id) . '
						AND topic = ' . $db->escapeString($topic));
			if ($dbResult->hasRecord()) {
				create_error('This topic exists already!');
			}

			$db->insert('alliance_thread_topic', [
				'game_id' => $db->escapeNumber($player->getGameID()),
				'alliance_id' => $db->escapeNumber($alliance_id),
				'thread_id' => $db->escapeNumber($thread_id),
				'topic' => $db->escapeString($topic),
				'alliance_only' => $db->escapeBoolean($allEyesOnly),
			]);
		}

		// and the body
		$db->insert('alliance_thread', [
			'game_id' => $db->escapeNumber($player->getGameID()),
			'alliance_id' => $db->escapeNumber($alliance_id),
			'thread_id' => $db->escapeNumber($thread_id),
			'reply_id' => $db->escapeNumber($reply_id),
			'text' => $db->escapeString($body),
			'sender_id' => $db->escapeNumber($player->getAccountID()),
			'time' => $db->escapeNumber(Epoch::time()),
		]);
		$db->replace('player_read_thread', [
			'account_id' => $db->escapeNumber($player->getAccountID()),
			'game_id' => $db->escapeNumber($player->getGameID()),
			'alliance_id' => $db->escapeNumber($alliance_id),
			'thread_id' => $db->escapeNumber($thread_id),
			'time' => $db->escapeNumber(Epoch::time() + 2),
		]);

		$this->lastPage->go();
	}

}
