<?php declare(strict_types=1);

namespace Smr\Pages\Layout;

use Smr\Epoch;

class CopyrightRenderer {

	public static function render(string $Version): void {
			$CurrentYear = date('Y', Epoch::time());

		?>
		<div class="right">
			SMR <?php echo $Version; ?>&copy;2007-<?php echo $CurrentYear; ?> Page and SMR<br />
			Kindly Hosted by <a href="http://www.fem.tu-ilmenau.de/" target="fem">FeM</a><br />
			Script runtime: <span id="rt"><?php echo number_format(microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'], 3); ?></span> seconds<br />
			<a href="imprint.php">[Imprint]</a>
		</div>
		<?php
	}

}
