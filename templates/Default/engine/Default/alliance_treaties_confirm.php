<br />
<div align="center">
	Are you sure you want to offer a treaty to <span class="yellow"><?php echo $AllianceName; ?></span> with the following conditions?
	<br />

	<ul class="noWrap left" style="display: inline-block"><?php
		foreach ($Terms as $Term => $Offered) {
			if ($Offered) { ?>
				<li><?php echo SmrTreaty::TYPES[$Term][0]; ?></li><?php
			}
		} ?>
	</ul>
	<br />
	<div class="buttonA">
		<a class="buttonA" href="<?php echo $YesHREF; ?>">Yes</a>
		&nbsp;&nbsp;
		<a class="buttonA" href="<?php echo $NoHREF; ?>">No</a>
	</div>
</div>
