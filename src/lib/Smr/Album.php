<?php declare(strict_types=1);

namespace Smr;

use Exception;
use Smr\Exceptions\AlbumNotFound;

readonly class Album {

	public ?string $location;
	public ?string $email;
	public ?string $website;
	public int $birthDay;
	public int $birthMonth;
	public int $birthYear;
	public string $otherInfo;
	public int $created;
	public int $lastChanged;
	public bool $isPictureDisabled;
	public string $approved;
	public int $pageViews;

	public static function getNextUnapproved(): self {
		$db = Database::getInstance();
		$dbResult = $db->read('SELECT *
					FROM album
					WHERE approved = \'TBC\'
					ORDER BY last_changed
					LIMIT 1');
		if ($dbResult->hasRecord()) {
			$dbRecord = $dbResult->record();
			return new self($dbRecord->getInt('account_id'), $dbRecord);
		}
		throw new AlbumNotFound('No albums to approve');
	}

	/**
	 * @return array<string, self>
	 */
	public static function getAllApproved(): array {
		$db = Database::getInstance();
		$dbResult = $db->read('SELECT album.*, hof_name FROM album JOIN account USING(account_id) WHERE approved = \'YES\'');
		$entries = [];
		foreach ($dbResult->records() as $dbRecord) {
			$hofName = $dbRecord->getString('hof_name');
			$entries[$hofName] = new self($dbRecord->getInt('account_id'), $dbRecord);
		}
		return $entries;
	}

	/**
	 * @return array<string, self>
	 */
	public static function getByHofName(string $hofNamePattern, bool $approved = true): array {
		$db = Database::getInstance();
		$dbResult = $db->read('SELECT album.*, hof_name FROM album JOIN account USING(account_id) WHERE hof_name LIKE :hof_name AND approved = :approved ORDER BY hof_name', [
			'hof_name' => $db->escapeString($hofNamePattern),
			'approved' => $approved ? 'YES' : 'NO',
		]);
		$entries = [];
		foreach ($dbResult->records() as $dbRecord) {
			$hofName = $dbRecord->getString('hof_name');
			$entries[$hofName] = new self($dbRecord->getInt('account_id'), $dbRecord);
		}
		return $entries;
	}

	public function __construct(
		public int $accountID,
		?DatabaseRecord $dbRecord = null,
	) {
		$db = Database::getInstance();
		if ($dbRecord === null) {
			$dbResult = $db->read('SELECT * FROM album WHERE account_id = :account_id', [
				'account_id' => $db->escapeNumber($accountID),
			]);
			if ($dbResult->hasRecord()) {
				$dbRecord = $dbResult->record();
			}
		}
		if ($dbRecord === null) {
			throw new AlbumNotFound('Album not found for account ID: ' . $accountID);
		}
		if ($accountID !== $dbRecord->getInt('account_id')) {
			throw new Exception('Inconsistent input arguments');
		}

		$this->location = $dbRecord->getNullableString('location');
		$this->email = $dbRecord->getNullableString('email');
		$this->website = $dbRecord->getNullableString('website');
		$this->birthDay = $dbRecord->getInt('day');
		$this->birthMonth = $dbRecord->getInt('month');
		$this->birthYear = $dbRecord->getInt('year');
		$this->otherInfo = $dbRecord->getString('other');
		$this->created = $dbRecord->getInt('created');
		$this->lastChanged = $dbRecord->getInt('last_changed');
		$this->isPictureDisabled = $dbRecord->getBoolean('disabled');
		$this->approved = $dbRecord->getString('approved');
		$this->pageViews = $dbRecord->getInt('page_views');
	}

	public function getDisplayBirthdate(): string {
		$date = 'N/A';
		if ($this->birthDay > 0 && $this->birthMonth > 0 && $this->birthYear > 0) {
			$date = $this->birthMonth . ' / ' . $this->birthDay . ' / ' . $this->birthYear;
		} elseif ($this->birthYear > 0) {
			$date = (string)$this->birthYear;
		}
		return $date;
	}

	public function getDisplayWebsite(): string {
		if ($this->website === null || $this->website === '') {
			return 'N/A';
		}
		return '<a href="' . $this->website . '" target="_new">' . $this->website . '</a>';
	}

	public function getDisplayOtherInfo(): string {
		return nl2br(htmlentities($this->otherInfo ?: 'N/A'));
	}

	public function getDisplayLocation(): string {
		return htmlentities($this->location ?: 'N/A');
	}

	public function getDisplayEmail(): string {
		return htmlentities($this->email ?: 'N/A');
	}

	public function getImageSrc(): string {
		if ($this->isPictureDisabled) {
			return 'images/album/disabled.jpg';
		}
		return 'upload/' . $this->accountID;
	}

	/**
	 * Returns all comments on this album entry in HTML-safe format.
	 *
	 * @return array<array{id: int, date: string, commenter: string, msg: string}>
	 */
	public function getComments(string $dateFormat): array {
		$db = Database::getInstance();
		$dbResult = $db->read('SELECT album_has_comments.*, account.hof_name
			FROM album_has_comments
			LEFT JOIN account ON album_has_comments.post_id = account.account_id
			WHERE album_id = :album_id', [
			'album_id' => $db->escapeNumber($this->accountID),
		]);
		$comments = [];
		foreach ($dbResult->records() as $dbRecord) {
			// Determine display name of commenter
			$commenterAccountID = $dbRecord->getInt('post_id');
			if ($commenterAccountID === 0) {
				$commenter = 'System';
			} else {
				$commenter = $dbRecord->getNullableString('hof_name');
			}
			if ($commenter === null) {
				throw new Exception('Could not find account ID: ' . $commenterAccountID);
			}

			// Player messages should be escaped, system messages should not
			// because they contain HTML.
			$msg = $dbRecord->getString('msg');
			if ($commenterAccountID !== 0) {
				$msg = htmlentities($msg);
			}

			$comments[] = [
				'id' => $dbRecord->getInt('comment_id'),
				'date' => date($dateFormat, $dbRecord->getInt('time')),
				'commenter' => htmlentities($commenter),
				'msg' => $msg,
			];
		}
		return $comments;
	}

}
