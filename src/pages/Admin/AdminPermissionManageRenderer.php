<?php declare(strict_types=1);

namespace Smr\Pages\Admin;

use Smr\Account;
use Smr\AdminPermissions;

class AdminPermissionManageRenderer {

	/**
	 * @param array<int, array{href: string, name: string}> $AdminLinks
	 */
	private static function renderAdminList(array $AdminLinks): void {
		?>
		List of Accounts with Permissions:<br />
		<small>Click to select</small>

		<ul><?php
		foreach ($AdminLinks as $AdminLink) { ?>
			<li>
				<a href="<?php echo $AdminLink['href']; ?>"><?php echo $AdminLink['name']; ?></a>
			</li><?php
		} ?>
		</ul>
		<br />

		<?php
	}

	/**
	 * @param array<int, array{href: string, name: string}> $AdminLinks
	 * @param array<int, string> $ValidatedAccounts
	 */
	public static function renderSelect(
		array $AdminLinks,
		array $ValidatedAccounts,
		string $SelectAdminHREF,
	): void {
		self::renderAdminList($AdminLinks); ?>
		Select an Account to add Permissions:
		<br /><br />
		<form method="POST" action="<?php echo $SelectAdminHREF; ?>">
			<select name="admin_id"><?php
			foreach ($ValidatedAccounts as $AccountID => $Login) { ?>
				<option value="<?php echo $AccountID; ?>"><?php echo $Login; ?></option><?php
			} ?>
			</select>
			&nbsp;&nbsp;&nbsp;
			<?php echo create_submit_display('Select'); ?>
		</form><?php
	}

	/**
	 * @param array<int, array{href: string, name: string}> $AdminLinks
	 * @param array<int, array<int, string>> $PermissionCategories
	 */
	public static function renderEdit(
		array $AdminLinks,
		Account $EditAccount,
		string $ProcessingHREF,
		string $CancelHREF,
		array $PermissionCategories,
	): void {
		self::renderAdminList($AdminLinks); ?>
		Change permissions for the Account of <u><?php echo $EditAccount->getLogin(); ?></u>!
		<form method="POST" action="<?php echo $ProcessingHREF; ?>"><?php
			foreach ($PermissionCategories as $categoryID => $permissions) { ?>
				<br /><h2><?php echo AdminPermissions::getCategoryName($categoryID); ?></h2>
				<div style="padding-left:20px;"><?php
					foreach ($permissions as $permissionID => $permissionName) {
						$checked = $EditAccount->hasPermission($permissionID) ? 'checked' : ''; ?>
						<input type="checkbox" name="permission_ids[]" value="<?php echo $permissionID; ?>" <?php echo $checked; ?> />
						<?php echo $permissionName; ?><br /><?php
					} ?>
				</div><?php
			} ?>
			<br />
			<?php echo create_submit_display('Change'); ?>
			&nbsp;&nbsp;&nbsp;
			<?php echo create_submit_link($CancelHREF, 'Select Another User'); ?>
		</form><?php
	}

}
