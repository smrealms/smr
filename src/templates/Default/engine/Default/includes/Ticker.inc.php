<?php
if (isset($Ticker)) { ?>
	<div id="ticker" class="ajax left" style="overflow:auto;height:8em;border:2px solid #0b8d45;"><?php
		if (is_array($Ticker)) {
			foreach ($Ticker as $Tick) {
				echo $Tick['Time']; ?>: &nbsp; <?php echo bbifyMessage($Tick['Message']); ?><br /><br /><?php
			}
		} else {
			?>Nothing to report<?php
		} ?>
	</div><br /><?php
} ?>