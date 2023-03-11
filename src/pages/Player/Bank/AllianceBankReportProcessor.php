<?php declare(strict_types=1);

namespace Smr\Pages\Player\Bank;

use Smr\AbstractPlayer;
use Smr\Database;
use Smr\Epoch;
use Smr\Page\PlayerPageProcessor;

class AllianceBankReportProcessor extends PlayerPageProcessor {

	public function __construct(
		private readonly int $allianceID,
		private readonly string $text
	) {}

	public function build(AbstractPlayer $player): never {
		// Send the bank report to the alliance message board
		$alliance_id = $this->allianceID;
		$text = $this->text;

		// Check if the "Bank Statement" thread exists yet
		$db = Database::getInstance();
		$dbResult = $db->read('SELECT thread_id FROM alliance_thread_topic WHERE game_id = :game_id AND alliance_id = :alliance_id AND topic = \'Bank Statement\' LIMIT 1', [
			'game_id' => $db->escapeNumber($player->getGameID()),
			'alliance_id' => $db->escapeNumber($alliance_id),
		]);

		if ($dbResult->hasRecord()) {
			// Update the existing "Bank Statement" thread
			$thread_id = $dbResult->record()->getInt('thread_id');
			$db->update(
				'alliance_thread',
				[
					'time' => Epoch::time(),
					'text' => $text,
				],
				[
					'thread_id' => $thread_id,
					'alliance_id' => $alliance_id,
					'game_id' => $player->getGameID(),
					'reply_id' => 1,
				],
			);
			$db->delete('player_read_thread', [
				'thread_id' => $thread_id,
				'game_id' => $player->getGameID(),
				'alliance_id' => $alliance_id,
			]);
		} else {
			// There is no "Bank Statement" thread yet
			$dbResult = $db->read('SELECT IFNULL(MAX(thread_id)+1, 0) AS next_id FROM alliance_thread_topic WHERE game_id = :game_id AND alliance_id = :alliance_id', [
				'game_id' => $db->escapeNumber($player->getGameID()),
				'alliance_id' => $db->escapeNumber($alliance_id),
			]);
			$thread_id = $dbResult->record()->getInt('next_id');
			$db->insert('alliance_thread_topic', [
				'game_id' => $player->getGameID(),
				'alliance_id' => $alliance_id,
				'thread_id' => $thread_id,
				'topic' => 'Bank Statement',
			]);
			$db->insert('alliance_thread', [
				'game_id' => $player->getGameID(),
				'alliance_id' => $alliance_id,
				'thread_id' => $thread_id,
				'reply_id' => 1,
				'text' => $text,
				'sender_id' => ACCOUNT_ID_BANK_REPORTER,
				'time' => Epoch::time(),
			]);
		}

		$container = new AllianceBankReport($alliance_id, reportSent: true);
		$container->go();
	}

}
