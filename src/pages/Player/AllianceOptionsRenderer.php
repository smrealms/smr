<?php declare(strict_types=1);

namespace Smr\Pages\Player;

class AllianceOptionsRenderer {

/** @param array<array{link: string, text: string}> $Links */
public static function render(array $Links): void {
if (count($Links) > 0) { // to prevent docblock from applying to for-loop
	foreach ($Links as $Link) { ?>
		<span class="big bold"><?php echo $Link['link']; ?></span>
		<br />
		<?php echo $Link['text']; ?>
		<br /><br /><?php
	}
}

}

}
