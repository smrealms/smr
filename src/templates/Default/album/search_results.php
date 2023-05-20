<?php declare(strict_types=1);

/**
 * @var array<string> $Nicks
 */

?>
<div class="center big">Please make a selection!</div>

<ul style="columns: 4;"><?php
	foreach ($Nicks as $Nick) { ?>
		<li><a href="?nick=<?php echo urlencode($Nick); ?>"><?php echo htmlentities($Nick); ?></a></li><?php
	} ?>
</ul>
