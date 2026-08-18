<?php declare(strict_types=1);

namespace Smr\Pages\Album;

use Closure;
use Smr\Epoch;

class SkeletonRenderer {

	/**
	 * @param array<string> $Letters
	 */
	public static function render(Closure $body, array $Letters): void {
		?>
		<!DOCTYPE html>
		<html>
			<head>
				<link rel="stylesheet" type="text/css" href="/<?php echo DEFAULT_CSS; ?>">
				<link rel="stylesheet" type="text/css" href="/<?php echo DEFAULT_CSS_COLOUR; ?>">
				<title>Space Merchant Realms - Photo Album</title>
				<meta http-equiv="pragma" content="no-cache">
			</head>

			<body>
				<div style="width: 700px; margin: 0 auto;">
					<h1 class="center">Space Merchant Realms - Photo Album</h1>
					<br />
					<div style="border: 4px solid #0B8D35; background-color: #06240E; padding: 10px;">
						<div class="center" style="font-size: 85%;">
							<div style="padding-bottom: 5px;">[
							<a href="?nick=<?php echo urlencode('%'); ?>">All</a>
							<?php
							foreach ($Letters as $letter) { ?>
								| <a href="?nick=<?php echo urlencode($letter . '%'); ?>"><?php echo $letter; ?></a><?php
							} ?>
							]</div>
							<form>
								<input type="text" name="search" size="10" required />
								<?php echo create_submit_display('Search'); ?>
							</form>
						</div>
						<hr class="center">
						<?php $body(); ?>
					</div>
					<br />
					<div class="left" style="font-size: 65%;">
						&copy; 2002-<?php echo date('Y', Epoch::time()); ?> by <a href="<?php echo URL; ?>"><?php echo URL; ?></a><br />
						Hosted by <a href="http://www.fem.tu-ilmenau.de/" target="fem">FeM</a>
					</div>
				</div>
			</body>
		</html>
		<?php
	}

}
