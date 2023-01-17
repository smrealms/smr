<?php declare(strict_types=1);

/**
 * @var array<array{link: string, text: string}> $Links
 */

if (count($Links) > 0) { // to prevent docblock from applying to for-loop
	foreach ($Links as $Link) { ?>
		<span class="big bold"><?php echo $Link['link']; ?></span>
		<br />
		<?php echo $Link['text']; ?>
		<br /><br /><?php
	}
}
