<?php
if (empty($PHP_OUTPUT)) {
	$this->includeTemplate($IncludeScript);
}
else {
	echo $PHP_OUTPUT;
}
?>
