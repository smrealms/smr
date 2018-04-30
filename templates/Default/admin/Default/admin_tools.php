<?php
if(isset($ErrorMessage)) {
	echo $ErrorMessage; ?><br /><br /><?php
}
if(isset($Message)) {
	echo $Message; ?><br /><br /><?php
}
if(isset($AdminPermissions)) { ?>
	<h1>Admin Tools</h1>
	<br /><?php
	foreach ($AdminPermissions as $Category => $Permissions) { ?>
		<h2><?php echo $Category; ?></h2>
		<ul><?php
		foreach ($Permissions as $Permission) { ?>
			<li><?php
				if($Permission['PermissionLink']!==false) {
					?><a href="<?php echo $Permission['PermissionLink']; ?>"><?php
				}
				echo $Permission['Name'];
				if($Permission['PermissionLink']!==false) {
					?></a><?php
				} ?>
			</li><?php
		} ?>
		</ul><?php
	}
} ?>
