<?php declare(strict_types=1);

namespace Smr\Pages\Admin;

use Smr\AdminPermissions;

class AdminToolsRenderer {

	/**
	 * @param array<int, list<array{Link: string|false, Name: string}>> $AdminPermissions
	 */
	public static function render(?string $ErrorMessage, ?string $Message, array $AdminPermissions): void {
		if ($ErrorMessage !== null) {
			echo $ErrorMessage; ?><br /><br /><?php
		}
		if ($Message !== null) {
			echo $Message; ?><br /><br /><?php
		}
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

	}

}
