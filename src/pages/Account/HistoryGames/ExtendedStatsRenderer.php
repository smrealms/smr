<?php declare(strict_types=1);

namespace Smr\Pages\Account\HistoryGames;

class ExtendedStatsRenderer {

/** @param array<string, string> $Links */
public static function render(array $Links): void {
?>
<div class="center">
	Click a link to view those stats.<br /><br /><?php
	foreach ($Links as $Category => $Href) { ?>
		<p><a href="<?php echo $Href; ?>" class="submitStyle"><?php echo $Category; ?></a></p><?php
	} ?>
</div>
<?php
}

}
