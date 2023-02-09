<?php declare(strict_types=1);

/**
 * @var array<string, string> $Links
 */

?>
<div class="center">
	Click a link to view those stats.<br /><br /><?php
	foreach ($Links as $Category => $Href) { ?>
		<p><a href="<?php echo $Href; ?>" class="submitStyle"><?php echo $Category; ?></a></p><?php
	} ?>
</div>
