<?php declare(strict_types=1);

/**
 * @var string $CurrentYear
 * @var string $Version
 */

?>
<div class="right">
	SMR <?php echo $Version; ?>&copy;2007-<?php echo $CurrentYear; ?> Page and SMR<br />
	Kindly Hosted by <a href="http://www.fem.tu-ilmenau.de/" target="fem">FeM</a><br />
	Script runtime: <span id="rt"><?php echo number_format(microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'], 3); ?></span> seconds<br />
	<a href="imprint.php">[Imprint]</a>
</div>
