<?php declare(strict_types=1);

namespace SmrTest\lib;

use PHPUnit\Framework\Attributes\CoversClass;
use Smr\Album;
use Smr\Database;
use SmrTest\BaseIntegrationSpec;

#[CoversClass(Album::class)]
class AlbumIntegrationTest extends BaseIntegrationSpec {

	protected function tablesToTruncate(): array {
		return ['album_has_comments'];
	}

	public function test_addComment_increments_comment_id(): void {
		$albumAccountID = 12345;
		$db = Database::getInstance();

		Album::addComment(
			albumAccountID: $albumAccountID,
			commentAccountID: 0,
			comment: 'First comment',
		);
		Album::addComment(
			albumAccountID: $albumAccountID,
			commentAccountID: 0,
			comment: 'Second comment',
		);

		$dbResult = $db->select(
			table: 'album_has_comments',
			criteria: ['album_id' => $albumAccountID],
			returnColumns: ['comment_id', 'msg'],
			orderBy: ['comment_id'],
		);
		$comments = [];
		foreach ($dbResult->records() as $dbRecord) {
			$comments[$dbRecord->getInt('comment_id')] = $dbRecord->getString('msg');
		}

		self::assertSame([
			1 => 'First comment',
			2 => 'Second comment',
		], $comments);
	}

}
