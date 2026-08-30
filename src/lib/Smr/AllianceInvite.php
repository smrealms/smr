<?php declare(strict_types=1);

namespace Smr;

use Smr\Exceptions\AllianceInvitationNotFound;

/**
 * Object interfacing with the alliance_invites_player table.
 */
class AllianceInvite {

	private readonly int $allianceID;
	private readonly int $gameID;
	private readonly int $receiverPlayerID;
	private readonly int $senderPlayerID;
	private readonly int $messageID;
	private readonly int $expires;

	public static function send(int $allianceID, int $gameID, int $receiverPlayerID, int $senderPlayerID, int $messageID, int $expires): void {
		$db = Database::getInstance();
		$db->insert('alliance_invites_player', [
			'game_id' => $gameID,
			'player_id' => $receiverPlayerID,
			'alliance_id' => $allianceID,
			'invited_by_player_id' => $senderPlayerID,
			'expires' => $expires,
			'message_id' => $messageID,
		]);
	}

	/**
	 * Get all unexpired invitations for the given alliance
	 *
	 * @return array<self>
	 */
	public static function getAll(int $allianceID, int $gameID): array {
		// Remove any expired invitations
		$db = Database::getInstance();
		$db->write('DELETE FROM alliance_invites_player WHERE expires < :now', [
			'now' => $db->escapeNumber(Epoch::time()),
		]);

		$dbResult = $db->select('alliance_invites_player', [
			'alliance_id' => $allianceID,
			'game_id' => $gameID,
		]);
		$invites = [];
		foreach ($dbResult->records() as $dbRecord) {
			$invites[] = new self($dbRecord);
		}
		return $invites;
	}

	/**
	 * Get the alliance invitation for a single recipient, if not expired
	 */
	public static function get(int $allianceID, int $gameID, int $receiverPlayerID): self {
		// Remove any expired invitations
		$db = Database::getInstance();
		$db->write('DELETE FROM alliance_invites_player WHERE expires < :now', [
			'now' => $db->escapeNumber(Epoch::time()),
		]);

		$dbResult = $db->select('alliance_invites_player', [
			'alliance_id' => $allianceID,
			'game_id' => $gameID,
			'player_id' => $receiverPlayerID,
		]);
		if ($dbResult->hasRecord()) {
			return new self($dbResult->record());
		}
		throw new AllianceInvitationNotFound();
	}

	public function __construct(DatabaseRecord $dbRecord) {
		$this->allianceID = $dbRecord->getInt('alliance_id');
		$this->gameID = $dbRecord->getInt('game_id');
		$this->receiverPlayerID = $dbRecord->getInt('player_id');
		$this->senderPlayerID = $dbRecord->getInt('invited_by_player_id');
		$this->messageID = $dbRecord->getInt('message_id');
		$this->expires = $dbRecord->getInt('expires');
	}

	public function delete(): void {
		$db = Database::getInstance();
		$db->delete('alliance_invites_player', [
			'alliance_id' => $this->allianceID,
			'game_id' => $this->gameID,
			'player_id' => $this->receiverPlayerID,
		]);
		$db->delete('message', [
			'message_id' => $this->messageID,
		]);
	}

	public function getSender(): Player {
		return Player::getPlayer($this->senderPlayerID);
	}

	public function getReceiver(): Player {
		return Player::getPlayer($this->receiverPlayerID);
	}

	public function getExpires(): int {
		return $this->expires;
	}

}
