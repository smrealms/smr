You punch the destination sector into your Jump Drive console.
Within moments, the onboard computer dictates the report in a reassuringly confident voice.

<br /><br />
It will cost <span class="red"><?php echo $TurnCost; ?></span> turns to jump to Sector #<?php echo $Target; ?>.
<?php
if ($MaxMisjump > 0) { ?>
	There is a possibility to misjump up to <?php echo $MaxMisjump . ' ' . pluralise('sector', $MaxMisjump); ?>.<?php
} else { ?>
	There is no possibility to misjump.<?php
} ?>


<br /><br />
<div class="center">
	<a href="<?php echo $JumpProcessingHREF; ?>">
		<button class="InputFields">Engage Jump (<?php echo $TurnCost; ?>)</button>
	</a>
</div>

<br />
<p class="center"><img src="images/logoff.jpg" width="324" height="216" alt=""></p>
