<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use Smr\Alliance;
use Smr\Database;
use Smr\Epoch;
use Smr\Page\PlayerPageProcessor;
use Smr\Page\ReusableTrait;
use Smr\Player;
use Smr\Request;

class AllianceMessageBoardAddProcessor extends PlayerPageProcessor {

	use ReusableTrait;

	public function __construct(
		private readonly int $allianceID,
		private readonly AllianceMessageBoard|AllianceMessageBoardView $lastPage,
		private readonly ?int $threadID = null,
	) {}

	public function build(Player $player): never {
		$db = Database::getInstance();

		$body = htmlentities(Request::get('body'), ENT_COMPAT, 'utf-8');
		$topic = Request::get('topic', ''); // only present for Create Thread
		$allEyesOnly = Request::has('allEyesOnly'); // only present for Create Thread

		$alliance_id = $this->allianceID;
		$alliance = Alliance::getAlliance($alliance_id, $player->getGameID());

		$action = Request::get('action');
		if ($action === 'Preview Thread' || $action === 'Preview Reply') {
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

		if ($body === '') {
			create_error('You must enter text!');
		}

		// if we don't have a thread id
		if ($this->threadID === null) {
			// get one
			$dbResult = $db->read('SELECT IFNULL(max(thread_id)+1, 0) AS next_thread_id FROM alliance_thread
						WHERE ' . Alliance::SQL, $alliance->SQLID);
			$thread_id = $dbResult->record()->getInt('next_thread_id');
		} else {
			$thread_id = $this->threadID;
		}

		// now get the next reply id
		$dbResult = $db->read('SELECT IFNULL(max(reply_id)+1, 0) AS next_reply_id FROM alliance_thread
					WHERE ' . Alliance::SQL . '
					AND thread_id = :thread_id', [
			...$alliance->SQLID,
			'thread_id' => $db->escapeNumber($thread_id),
		]);
		$reply_id = $dbResult->record()->getInt('next_reply_id');

		// only add the topic if it's the first reply
		if ($reply_id === 0) {
			if ($topic === '') {
				create_error('You must enter a topic!');
			}

			if (strlen($topic) > 255) {
				create_error('Topic can\'t be longer than 255 chars!');
			}

			// test if this topic already exists
			$dbResult = $db->select('alliance_thread_topic', [
				...$alliance->SQLID,
				'topic' => $topic,
			]);
			if ($dbResult->hasRecord()) {
				create_error('This topic exists already!');
			}

			$db->insert('alliance_thread_topic', [
				...$alliance->SQLID,
				'thread_id' => $thread_id,
				'topic' => $topic,
				'alliance_only' => $db->escapeBoolean($allEyesOnly),
			]);
		}

		// and the body
		$db->insert('alliance_thread', [
			...$alliance->SQLID,
			'thread_id' => $thread_id,
			'reply_id' => $reply_id,
			'text' => $body,
			'sender_id' => $player->getAccountID(),
			'time' => Epoch::time(),
		]);
		$db->replace('player_read_thread', [
			...$player->SQLID,
			'alliance_id' => $alliance_id,
			'thread_id' => $thread_id,
			'time' => Epoch::time() + 2,
		]);

		$this->lastPage->go();
	}

}
