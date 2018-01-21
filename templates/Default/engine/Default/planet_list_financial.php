<?php
if (!$CanViewBonds) { ?>
	<div align="center">
		You do not have permission to view planet financials!
	</div><?php
} else {
	$this->includeTemplate('planet_list.inc', array('ExtraInclude'=>'includes/PlanetListFinancial.inc'));
}
?>
