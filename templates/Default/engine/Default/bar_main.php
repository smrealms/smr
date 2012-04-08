<?php
if(isset($IncludeScript)) {
	$this->includeTemplate($IncludeScript);
}
else {
	echo $PHP_OUTPUT;
}
?>