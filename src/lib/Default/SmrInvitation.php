<?php declare(strict_types=1);

use Smr\Database;
use Smr\DatabaseRecord;
use Smr\Epoch;
use Smr\Exceptions\AllianceInvitationNotFound;

/**
 * Object interfacing with the alliance_invites_player table.
 */
class SmrInvitation {

	private readonly int $allianceID;
	private readonly int $gameID;
	private readonly int $receiverAccountID;
	private readonly int $senderAccountID;
	private readonly int $messageID;
	private readonly int $expires;

	public static function send(int $allianceID, int $gameID, int $receiverAccountID, int $senderAccountID, int $messageID, int $expires): void {
		$db = Database::getInstance();
		$db->insert('alliance_invites_player', [
			'game_id' => $db->escapeNumber($gameID),
			'account_id' => $db->escapeNumber($receiverAccountID),
			'alliance_id' => $db->escapeNumber($allianceID),
			'invited_by_id' => $db->escapeNumber($senderAccountID),
			'expires' => $db->escapeNumber($expires),
			'message_id' => $db->escapeNumber($messageID),
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
		$db->write('DELETE FROM alliance_invites_player WHERE expires < ' . $db->escapeNumber(Epoch::time()));

		$dbResult = $db->read('SELECT * FROM alliance_invites_player WHERE alliance_id=' . $db->escapeNumber($allianceID) . ' AND game_id=' . $db->escapeNumber($gameID));
		$invites = [];
		foreach ($dbResult->records() as $dbRecord) {
			$invites[] = new self($dbRecord);
		}
		return $invites;
	}

	/**
	 * Get the alliance invitation for a single recipient, if not expired
	 */
	public static function get(int $allianceID, int $gameID, int $receiverAccountID): self {
		// Remove any expired invitations
		$db = Database::getInstance();
		$db->write('DELETE FROM alliance_invites_player WHERE expires < ' . $db->escapeNumber(Epoch::time()));

		$dbResult = $db->read('SELECT * FROM alliance_invites_player WHERE alliance_id=' . $db->escapeNumber($allianceID) . ' AND game_id=' . $db->escapeNumber($gameID) . ' AND account_id=' . $db->escapeNumber($receiverAccountID));
		if ($dbResult->hasRecord()) {
			return new self($dbResult->record());
		}
		throw new AllianceInvitationNotFound();
	}

	public function __construct(DatabaseRecord $dbRecord) {
		$this->allianceID = $dbRecord->getInt('alliance_id');
		$this->gameID = $dbRecord->getInt('game_id');
		$this->receiverAccountID = $dbRecord->getInt('account_id');
		$this->senderAccountID = $dbRecord->getInt('invited_by_id');
		$this->messageID = $dbRecord->getInt('message_id');
		$this->expires = $dbRecord->getInt('expires');
	}

	public function delete(): void {
		$db = Database::getInstance();
		$db->write('DELETE FROM alliance_invites_player WHERE alliance_id=' . $db->escapeNumber($this->allianceID) . ' AND game_id=' . $db->escapeNumber($this->gameID) . ' AND account_id=' . $db->escapeNumber($this->receiverAccountID));
		$db->write('DELETE FROM message WHERE message_id=' . $db->escapeNumber($this->messageID));
	}

	public function getSender(): AbstractSmrPlayer {
		return SmrPlayer::getPlayer($this->senderAccountID, $this->gameID);
	}

	public function getReceiver(): AbstractSmrPlayer {
		return SmrPlayer::getPlayer($this->receiverAccountID, $this->gameID);
	}

	public function getExpires(): int {
		return $this->expires;
	}

}
