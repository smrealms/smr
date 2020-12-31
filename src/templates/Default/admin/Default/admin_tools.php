<?php
if (isset($ErrorMessage)) {
	echo $ErrorMessage; ?><br /><br /><?php
}
if (isset($Message)) {
	echo $Message; ?><br /><br /><?php
}
if (isset($AdminPermissions)) { ?>
	<h1>Admin Tools</h1>
	<br /><?php
	foreach ($AdminPermissions as $CategoryID => $Permissions) { ?>
		<h2><?php echo AdminPermissions::getCategoryName($CategoryID); ?></h2>
		<ul><?php
		foreach ($Permissions as $Permission) { ?>
			<li><?php
				if ($Permission['Link'] !== false) { ?>
					<a href="<?php echo $Permission['Link']; ?>"><?php echo $Permission['Name']; ?></a><?php
				} else { ?>
					<i><?php echo $Permission['Name']; ?></i><?php
				} ?>
			</li><?php
		} ?>
		</ul><?php
	}
} ?>
