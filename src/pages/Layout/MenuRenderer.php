<?php declare(strict_types=1);

namespace Smr\Pages\Layout;

use Smr\Template;

class MenuRenderer {

public static function render(Template $template): void {
// If there are no menu items, we still want a blank menu bar if there is a page topic
if ($template->menuItems !== null || $template->subMenuBar || $template->pageTopic !== null) { ?>
	<div class="bar1">
		<div><?php
			if ($template->menuItems !== null) { ?>
				<span class="noWrap"><?php
					foreach ($template->menuItems as $number => $MenuItem) {
						if ($number > 0) {
							?></span> | <span class="noWrap"><?php
						}
						?><a class="nav" href="<?php echo $MenuItem['Link']; ?>"><?php echo $MenuItem['Text']; ?></a><?php
					}?>
				</span><?php
			} elseif ($template->subMenuBar !== null) {
				echo $template->subMenuBar;
			} ?>
		</div>
	</div>
	<br /><?php
}

}

}
