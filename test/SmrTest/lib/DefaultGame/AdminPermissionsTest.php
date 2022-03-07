<?php declare(strict_types=1);

namespace SmrTest\lib\DefaultGame;

use Smr\AdminPermissions;

/**
 * @covers Smr\AdminPermissions
 */
class AdminPermissionsTest extends \PHPUnit\Framework\TestCase {

	public function test_getPermissionInfo(): void {
		// Spot check one of the permissions
		$expected = ['Enable Games', 'admin/enable_game.php', 5];
		self::assertSame($expected, AdminPermissions::getPermissionInfo(33));
	}

	public function test_getPermissionByCategory(): void {
		// Spot check one of the categories
		$expected = [
			19 => 'Approve Photo Album',
			20 => 'Moderate Photo Album',
			27 => 'Can Moderate Feature Requests',
			36 => 'Display Admin Tag',
		];
		self::assertSame($expected, AdminPermissions::getPermissionsByCategory()[2]);
	}

	public function test_getCategoryName(): void {
		// Spot check one of the categories
		self::assertSame('Administrative', AdminPermissions::getCategoryName(3));
	}

}
