<?php
if (isset($MenuItems) || isset($SubMenuBar)) { ?>
	<div class="bar1">
		<div><?php
			if (isset($MenuItems)) { ?>
				<span class="noWrap"><?php
					foreach ($MenuItems as $number => $MenuItem) {
						if (isset($MenuItem['Link'])) {
							if ($number > 0) {
								?></span> | <span class="noWrap"><?php
							}
							?><a class="nav" href="<?php echo $MenuItem['Link']; ?>"><?php echo $MenuItem['Text']; ?></a><?php
						}
					}?>
				</span><?php
			} elseif (isset($SubMenuBar)) {
				echo $SubMenuBar;
			} ?>
		</div>
	</div>
	<br /><?php
}
