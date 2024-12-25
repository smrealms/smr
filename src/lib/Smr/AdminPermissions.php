<?php declare(strict_types=1);

namespace Smr;

use Smr\Pages\Admin\AccountEditSearch;
use Smr\Pages\Admin\AdminMessageSendSelect;
use Smr\Pages\Admin\AdminPermissionManage;
use Smr\Pages\Admin\AlbumApprove;
use Smr\Pages\Admin\AlbumModerateSelect;
use Smr\Pages\Admin\AnnouncementCreate;
use Smr\Pages\Admin\AnonBankViewSelect;
use Smr\Pages\Admin\ChangelogAdd;
use Smr\Pages\Admin\CheatingShipCheck;
use Smr\Pages\Admin\CombatSimulator;
use Smr\Pages\Admin\DatabaseCleanup;
use Smr\Pages\Admin\EditLocations;
use Smr\Pages\Admin\EnableGame;
use Smr\Pages\Admin\FormOpen;
use Smr\Pages\Admin\GameDelete;
use Smr\Pages\Admin\IpView;
use Smr\Pages\Admin\LogConsole;
use Smr\Pages\Admin\ManageDraftLeaders;
use Smr\Pages\Admin\ManagePostEditors;
use Smr\Pages\Admin\MessageBoxView;
use Smr\Pages\Admin\NewsletterSend;
use Smr\Pages\Admin\NpcManage;
use Smr\Pages\Admin\ReportedMessageView;
use Smr\Pages\Admin\ServerStatus;
use Smr\Pages\Admin\UniGen\CreateGame;
use Smr\Pages\Admin\VoteCreate;
use Smr\Pages\Admin\WordFilter;

class AdminPermissions {

	// The array keys must not be changed because they are referred to
	// in the `account_has_permission` database table.
	// Info is [Permission Name, Page to Link, Category].
	private const array PERMISSION_TABLE = [
		1 => ['Manage Admin Permissions', AdminPermissionManage::class, 3],
		2 => ['Database Cleanup', DatabaseCleanup::class, 3],
		3 => ['Server Open/Close', ServerStatus::class, 3],
		4 => ['Delete Game', GameDelete::class, 5],
		5 => ['Create Announcement', AnnouncementCreate::class, 3],
		6 => ['Send Message', AdminMessageSendSelect::class, 3],
		7 => ['View Reported Messages', ReportedMessageView::class, 1],
		8 => ['Edit Account', AccountEditSearch::class, 1],
		9 => ['Multi Tools', IpView::class, 1],
		12 => ['Cheating Ship Check', CheatingShipCheck::class, 1],
		16 => ['Log Console', LogConsole::class, 1],
		17 => ['Send Newsletter', NewsletterSend::class, 3],
		18 => ['Form Access', FormOpen::class, 3],
		19 => ['Approve Photo Album', AlbumApprove::class, 2],
		20 => ['Moderate Photo Album', AlbumModerateSelect::class, 2],
		21 => ['Manage ChangeLog', ChangelogAdd::class, 3],
		22 => ['Anon Account View', AnonBankViewSelect::class, 1],
		23 => ['Word Filter', WordFilter::class, 1],
		24 => ['Combat Simulator', CombatSimulator::class, 4],
		25 => ['Edit Locations', EditLocations::class, 4],
		26 => ['View Message Boxes', MessageBoxView::class, 1],
		27 => ['Can Moderate Feature Requests', null, 2],
		28 => ['Can Edit Alliance Descriptions', null, 1],
		30 => ['Universe Generator', CreateGame::class, 5],
		31 => ['Create Vote', VoteCreate::class, 3],
		32 => ['Can Edit Enabled Games', null, 5],
		33 => ['Enable Games', EnableGame::class, 5],
		34 => ['Manage Galactic Post Editors', ManagePostEditors::class, 5],
		35 => ['Manage Draft Leaders', ManageDraftLeaders::class, 5],
		36 => ['Display Admin Tag', null, 2],
		37 => ['Manage NPCs', NpcManage::class, 5],
	];

	private const array PERMISSION_CATEGORIES = [
		1 => 'Monitor Players',
		2 => 'Community Services',
		3 => 'Administrative',
		4 => 'Miscellaneous',
		5 => 'Manage Games',
	];

	/**
	 * Returns the info for the admin permission with the given ID.
	 *
	 * @return array{string, ?class-string<\Smr\Page\Page>, int}
	 */
	public static function getPermissionInfo(int $permissionID): array {
		return self::PERMISSION_TABLE[$permissionID];
	}

	/**
	 * Returns a list of all permissions with ID keys and name values,
	 * grouped by the category ID of the permission.
	 *
	 * @return array<int, array<int, string>>
	 */
	public static function getPermissionsByCategory(): array {
		$result = [];
		foreach (self::PERMISSION_TABLE as $permissionID => $info) {
			$categoryID = $info[2];
			$result[$categoryID][$permissionID] = $info[0];
		}
		return $result;
	}

	public static function getCategoryName(int $categoryID): string {
		return self::PERMISSION_CATEGORIES[$categoryID];
	}

}
