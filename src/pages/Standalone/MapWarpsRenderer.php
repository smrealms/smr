<?php declare(strict_types=1);

/**
 * @var string $GameName
 * @var non-empty-string $GraphData
 */

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
		<script src="/js/map_warps.js"></script>
	</body>
</html>
