<?php declare(strict_types=1);

namespace SmrTest\lib\DefaultGame;

use PHPUnit\Framework\TestCase;
use Smr\AdminPermissions;
use Smr\Pages\Admin\EnableGame;

/**
 * @covers Smr\AdminPermissions
 */
class AdminPermissionsTest extends TestCase {

	public function test_getPermissionInfo(): void {
		// Spot check one of the permissions
		$expected = ['Enable Games', EnableGame::class, 5];
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
