<?php declare(strict_types=1);

namespace Smr\Pages\Standalone;

class MapWarpsRenderer {

	/**
	 * @param non-empty-string $GraphData
	 */
	public static function render(string $GameName, string $GraphData): void {
		?>
		<!DOCTYPE html>
		<html>
			<head>
				<title><?php echo PAGE_TITLE . ': ' . $GameName; ?></title>
				<meta charset="utf-8">
				<style>
					body { background-image: url("images/stars2.png"); }
				</style>
			</head>

			<body>
				<script src="https://d3js.org/d3.v7.min.js"></script>
				<script src="<?php echo JQUERY_URL; ?>"></script>
				<script>
					const graph = <?php echo $GraphData; ?>;
				</script>
				<script src="<?php echo asset_url('/js/map_warps.js'); ?>"></script>
			</body>
		</html>
		<?php
	}

}
