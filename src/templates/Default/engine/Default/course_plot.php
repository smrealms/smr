<?php declare(strict_types=1);

/**
 * @var Smr\Player $ThisPlayer
 * @var Smr\Template $this
 * @var string $PlotCourseFormLink
 * @var string $PlotNearestFormLink
 * @var string $PlotToNearestHREF
 * @var array<\Smr\PlotGroup> $AllXTypes
 * @var array<int|string, string> $XTypeOptions
 * @var array<int, \Smr\StoredDestination> $StoredDestinations
 * @var string $ManageDestination
 */

?>
<a href="<?php echo WIKI_URL; ?>/game-guide/how-your-ship-works" target="_blank"><img style="float: right;" src="images/silk/help.png" width="16" height="16" alt="Wiki Link" title="Goto SMR Wiki: How Your Ship Works"/></a>
<form class="standard" id="PlotCourseForm" method="POST" action="<?php echo $PlotCourseFormLink; ?>">
	<h2>Conventional</h2>
	<div class="standard">Enter a destination sector.</div>
	<table class="nobord nohpad">
		<tr>
			<td>From:&nbsp;</td>
			<td><input required type="number" size="5" name="from" maxlength="5" class="center" value="<?php echo $ThisPlayer->getSectorID(); ?>"></td>
			<td>&nbsp;&nbsp;&nbsp;&nbsp;To:&nbsp;</td>
			<td><input required type="number" size="5" name="to" maxlength="5" class="center"></td>
			<td>&nbsp;&nbsp;&nbsp;&nbsp;<input type="submit" name="action" value="Plot Course"></td>
		</tr>
	</table>
</form><?php

$this->includeTemplate('includes/JumpDrive.inc.php'); ?>

<br />
<h2>Plot To Nearest</h2>
<div class="standard">Select a location to plot to. You are only able to plot to sectors you have explored.</div><br />
<form class="standard" id="SelectXTypeForm" method="POST" action="<?php echo $PlotToNearestHREF; ?>">
	<select name="xtype" onchange="this.form.submit()"><?php
	foreach ($AllXTypes as $EachXType) {
		?><option value="<?php echo $EachXType->value; ?>"<?php if (isset($XType) && $EachXType === $XType) { ?> selected="selected"<?php } ?>><?php echo $EachXType->value; ?></option><?php
	} ?>
	</select>&nbsp;
	<input type="submit" value="Select" />
</form><?php
if (isset($XType)) { ?>
	<form class="standard" id="PlotNearestForm" method="POST" action="<?php echo $PlotNearestFormLink; ?>">
		<input type="hidden" name="xtype" value="<?php echo $XType->value; ?>" /><br />
		<select name="X" onchange="this.form.submit()"><?php
			foreach ($XTypeOptions as $Value => $Name) { ?>
				<option value="<?php echo $Value; ?>"><?php echo $Name; ?></option><?php
			} ?>
		</select>&nbsp;
		<input type="submit" value="Go" />
	</form><?php
} ?>

<br />
<br />
<h2>Stored destinations</h2>
Add new destinations below. Stored destinations can be organized by dragging.

<div id="droppableObject" class="savedDestinationArea"><?php
	foreach ($StoredDestinations as $sectorID => $SD) { ?>
		<div class="draggableObject savedDestination"
			style="top:<?php echo $SD->offsetTop; ?>px; left:<?php echo $SD->offsetLeft; ?>px"
			data-sector-id="<?php echo $sectorID; ?>">
			<a href="javascript:processCourse(<?php echo $sectorID; ?>)"> <?php echo $SD->getDisplayName(); ?></a>
			<a href="javascript:processRemove(<?php echo $sectorID; ?>)"> <img src="images/silk/cross.png" width="16" height="16" alt="X" title="Delete Saved Sector"/></a>
		</div><?php
	} ?>
</div>
<?php $this->addJavascriptSource('/js/course_plot.js'); ?>

<br/><br/>
<h2>Add new destination</h2>

<form class="standard" id="manageDestination" method="POST" action="<?php echo $ManageDestination; ?>">
	<label for="sectorId">Sector:</label>&nbsp;<input type="number" name="sectorId" style="width:75px" min="1" required /> &nbsp; &nbsp;
	<label for="label">Label:</label>&nbsp;<input type="text" name="label" value="" size="14"/> &nbsp;
	<input type="hidden" name="type" value="add"/>
	<input type="hidden" name="offsetTop" value="0"/>
	<input type="hidden" name="offsetLeft" value="0"/>
	<input type="submit" value="Add Destination"/>
</form>

<form  id="plotCourseForm" method="POST" action="<?php echo $PlotCourseFormLink; ?>">
	<input type="hidden" name="from" value="<?php echo $ThisPlayer->getSectorID(); ?>"/>
	<input type="hidden" name="to" value="1"/>
</form>
