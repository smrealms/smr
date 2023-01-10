<?php declare(strict_types=1);

foreach ($Links as $Link) { ?>
	<span class="big bold"><?php echo $Link['link']; ?></span>
	<br />
	<?php echo $Link['text']; ?>
	<br /><br /><?php
}
?>
