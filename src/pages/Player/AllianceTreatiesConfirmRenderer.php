<?php declare(strict_types=1);

use Smr\Treaty;

/**
 * @var string $YesHREF
 * @var string $NoHREF
 * @var string $AllianceName
 * @var array<string, bool> $Terms
 */

?>
<br />
<div class="center">
	Are you sure you want to offer a treaty to <span class="yellow"><?php echo $AllianceName; ?></span> with the following conditions?
	<br />

	<ul class="noWrap left" style="display: inline-block"><?php
		foreach ($Terms as $Term => $Offered) {
			if ($Offered) { ?>
				<li><?php echo Treaty::TYPES[$Term][0]; ?></li><?php
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
