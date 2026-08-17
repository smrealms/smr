<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use Smr\Pages\Shared\JumpDriveRenderer;
use Smr\Player;
use Smr\PlotGroup;
use Smr\Template;

class PlotCourseRenderer {

	/**
	 * @param array<int|string, string> $XTypeOptions
	 * @param array<int, \Smr\StoredDestination> $StoredDestinations
	 * @param array<\Smr\PlotGroup> $AllXTypes
	 */
	public static function render(
		Template $template,
		string $PlotCourseFormLink,
		string $PlotNearestFormLink,
		?SectorJumpProcessor $JumpDrivePage,
		string $PlotToNearestHREF,
		PlotGroup $XType,
		array $AllXTypes,
		array $XTypeOptions,
		array $StoredDestinations,
		string $ManageDestination,
		Player $ThisPlayer,
	): void {
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
					<td>&nbsp;&nbsp;&nbsp;&nbsp;<?php echo create_submit_display('Plot Course'); ?></td>
				</tr>
			</table>
		</form><?php

		JumpDriveRenderer::render(
			ThisShip: $ThisPlayer->getShip(),
			JumpDrivePage: $JumpDrivePage,
		); ?>

		<br />
		<h2>Plot To Nearest</h2>
		<div class="standard">Select a location to plot to. You are only able to plot to sectors you have explored.</div><br />
		<form class="standard" id="SelectXTypeForm" method="POST" action="<?php echo $PlotToNearestHREF; ?>">
			<select name="xtype" onchange="this.form.submit()"><?php
			foreach ($AllXTypes as $EachXType) {
				?><option value="<?php echo $EachXType->value; ?>"<?php if ($EachXType === $XType) { ?> selected="selected"<?php } ?>><?php echo $EachXType->value; ?></option><?php
			} ?>
			</select>&nbsp;
			<?php echo create_submit_display('Select'); ?>
		</form>

			<form class="standard" id="PlotNearestForm" method="POST" action="<?php echo $PlotNearestFormLink; ?>">
				<input type="hidden" name="xtype" value="<?php echo $XType->value; ?>" /><br />
				<select name="X" onchange="this.form.submit()"><?php
					foreach ($XTypeOptions as $Value => $Name) { ?>
						<option value="<?php echo $Value; ?>"><?php echo $Name; ?></option><?php
					} ?>
				</select>&nbsp;
				<?php echo create_submit_display('Go'); ?>
			</form>

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
		<?php $template->addJavascriptSource('/js/course_plot.js'); ?>

		<br/><br/>
		<h2>Add new destination</h2>

		<form class="standard" id="manageDestination" method="POST" action="<?php echo $ManageDestination; ?>">
			<label for="sectorId">Sector:</label>&nbsp;<input type="number" name="sectorId" style="width:75px" min="1" required /> &nbsp; &nbsp;
			<label for="label">Label:</label>&nbsp;<input type="text" name="label" value="" size="14"/> &nbsp;
			<input type="hidden" name="type" value="add"/>
			<input type="hidden" name="offsetTop" value="0"/>
			<input type="hidden" name="offsetLeft" value="0"/>
			<?php echo create_submit_display('Add Destination'); ?>
		</form>

		<form  id="plotCourseForm" method="POST" action="<?php echo $PlotCourseFormLink; ?>">
			<input type="hidden" name="from" value="<?php echo $ThisPlayer->getSectorID(); ?>"/>
			<input type="hidden" name="to" value="1"/>
		</form>

		<?php
	}

}
