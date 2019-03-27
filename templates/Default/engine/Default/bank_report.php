<div class="center"><?php
	if (isset($SendReportHREF)) { ?>
		<div class="buttonA">
			<a class="buttonA" href="<?php echo $SendReportHREF; ?>">Send Report to Alliance</a>
		</div><?php
	} else { ?>
		A statement has been sent to the alliance.<?php
	} ?>
</div>
<?php echo $BankReport; ?>
