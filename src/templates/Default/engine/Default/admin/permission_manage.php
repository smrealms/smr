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
if (!isset($EditAccount)) { ?>
	Select an Account to add Permissions:
	<br /><br />
	<form method="POST" action="<?php echo $SelectAdminHREF; ?>">
		<select name="admin_id"><?php
		foreach ($ValidatedAccounts as $AccountID => $Login) { ?>
			<option value="<?php echo $AccountID; ?>"><?php echo $Login; ?></option><?php
		} ?>
		</select>
		&nbsp;&nbsp;&nbsp;
		<input type="submit" value="Select" />
	</form><?php
} else { ?>
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
		<input type="submit" name="action" value="Change" />
		&nbsp;&nbsp;&nbsp;
		<input type="submit" name="action" value="Select Another User" />
	</form><?php
} ?>
