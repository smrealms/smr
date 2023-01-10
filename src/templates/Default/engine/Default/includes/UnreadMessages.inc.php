<?php declare(strict_types=1);

?>
<span id="message_area"><?php
	foreach ($UnreadMessages as $UnreadMessage) { ?>
		<a href="<?php echo $UnreadMessage['href']; ?>"><img src="<?php echo $UnreadMessage['img']; ?>" width="32" height="32" alt="<?php echo $UnreadMessage['alt']; ?>" /></a>
		<span class="small"><?php echo $UnreadMessage['num']; ?></span><?php
	}
	if (count($UnreadMessages) > 0) { ?>
		<br /><?php
	} ?>
</span>
