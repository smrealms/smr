<?php declare(strict_types=1);

/**
 * Object interfacing with the alliance_invites_player table.
 */
class SmrInvitation {

	private int $allianceID;
	private int $gameID;
	private int $receiverAccountID;
	private int $senderAccountID;
	private int $messageID;
	private int $expires;

	static public function send(int $allianceID, int $gameID, int $receiverAccountID, int $senderAccountID, int $messageID, int $expires) : void {
		$db = Smr\Database::getInstance();
		$db->write('INSERT INTO alliance_invites_player (game_id, account_id, alliance_id, invited_by_id, expires, message_id) VALUES(' . $db->escapeNumber($gameID) . ', ' . $db->escapeNumber($receiverAccountID) . ', ' . $db->escapeNumber($allianceID) . ', ' . $db->escapeNumber($senderAccountID) . ', ' . $db->escapeNumber($expires) . ', ' . $db->escapeNumber($messageID) . ')');
	}

	/**
	 * Get all unexpired invitations for the given alliance
	 */
	static public function getAll(int $allianceID, int $gameID) : array {
		// Remove any expired invitations
		$db = Smr\Database::getInstance();
		$db->write('DELETE FROM alliance_invites_player WHERE expires < ' . $db->escapeNumber(Smr\Epoch::time()));

		$dbResult = $db->read('SELECT * FROM alliance_invites_player WHERE alliance_id=' . $db->escapeNumber($allianceID) . ' AND game_id=' . $db->escapeNumber($gameID));
		$invites = [];
		foreach ($dbResult->records() as $dbRecord) {
			$invites[] = new SmrInvitation($dbRecord);
		}
		return $invites;
	}

	/**
	 * Get the alliance invitation for a single recipient, if not expired
	 */
	static public function get(int $allianceID, int $gameID, int $receiverAccountID) : SmrInvitation {
		// Remove any expired invitations
		$db = Smr\Database::getInstance();
		$db->write('DELETE FROM alliance_invites_player WHERE expires < ' . $db->escapeNumber(Smr\Epoch::time()));

		$dbResult = $db->read('SELECT * FROM alliance_invites_player WHERE alliance_id=' . $db->escapeNumber($allianceID) . ' AND game_id=' . $db->escapeNumber($gameID) . ' AND account_id=' . $db->escapeNumber($receiverAccountID));
		if ($dbResult->hasRecord()) {
			return new SmrInvitation($dbResult->record());
		}
		throw new Smr\Exceptions\AllianceInvitationNotFound;
	}

	public function __construct(Smr\DatabaseRecord $dbRecord) {
		$this->allianceID = $dbRecord->getInt('alliance_id');
		$this->gameID = $dbRecord->getInt('game_id');
		$this->receiverAccountID = $dbRecord->getInt('account_id');
		$this->senderAccountID = $dbRecord->getInt('invited_by_id');
		$this->messageID = $dbRecord->getInt('message_id');
		$this->expires = $dbRecord->getInt('expires');
	}

	public function delete() : void {
		$db = Smr\Database::getInstance();
		$db->write('DELETE FROM alliance_invites_player WHERE alliance_id=' . $db->escapeNumber($this->allianceID) . ' AND game_id=' . $db->escapeNumber($this->gameID) . ' AND account_id=' . $db->escapeNumber($this->receiverAccountID));
		$db->write('DELETE FROM message WHERE message_id=' . $db->escapeNumber($this->messageID));
	}

	public function getSender() : SmrPlayer {
		return SmrPlayer::getPlayer($this->senderAccountID, $this->gameID);
	}

	public function getReceiver() : SmrPlayer {
		return SmrPlayer::getPlayer($this->receiverAccountID, $this->gameID);
	}

	public function getExpires() : int {
		return $this->expires;
	}

}
