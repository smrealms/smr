<?php declare(strict_types=1);

if (!$CanViewBonds) { ?>
	<div class="center">
		You do not have permission to view planet financials!
	</div><?php
} else {
	$this->includeTemplate('planet_list.inc.php', ['ExtraInclude' => 'includes/PlanetListFinancial.inc.php']);
}
