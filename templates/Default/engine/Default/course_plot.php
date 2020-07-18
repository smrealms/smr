<a href="<?php echo WIKI_URL; ?>/game-guide/how-your-ship-works" target="_blank"><img style="float: right;" src="images/silk/help.png" width="16" height="16" alt="Wiki Link" title="Goto SMR Wiki: How Your Ship Works"/></a>
<form class="standard" id="PlotCourseForm" method="POST" action="<?php echo $PlotCourseFormLink; ?>">
	<h2>Conventional</h2>
	<div class="standard">Enter a destination sector.</div>
	<table class="nobord nohpad">
		<tr>
			<td>From:&nbsp;</td>
			<td><input type="number" size="5" name="from" maxlength="5" class="center" value="<?php echo $ThisPlayer->getSectorID(); ?>"></td>
			<td>&nbsp;&nbsp;&nbsp;&nbsp;To:&nbsp;</td>
			<td><input type="number" size="5" name="to" maxlength="5" class="center"></td>
			<td>&nbsp;&nbsp;&nbsp;&nbsp;<input type="submit" name="action" value="Plot Course"></td>
		</tr>
	</table>
</form><?php

$this->includeTemplate('includes/JumpDrive.inc'); ?>

<br />
<h2>Plot To Nearest</h2>
<div class="standard">Select a location to plot to. You are only able to plot to sectors you have explored.</div><br />
<form class="standard" id="SelectXTypeForm" method="POST" action="<?php echo $PlotToNearestHREF; ?>">
	<select name="xtype" onchange="this.form.submit()"><?php
	foreach ($AllXTypes as $EachXType) {
		?><option value="<?php echo $EachXType; ?>"<?php if (isset($XType) && $EachXType == $XType) { ?> selected="selected"<?php } ?>><?php echo $EachXType; ?></option><?php
	} ?>
	</select>&nbsp;
	<input type="submit" value="Select" />
</form><?php
if (isset($XType)) { ?>
	<form class="standard" id="PlotNearestForm" method="POST" action="<?php echo $PlotNearestFormLink; ?>">
		<input type="hidden" name="xtype" value="<?php echo $XType; ?>" /><br />
		<select name="X" onchange="this.form.submit()"><?php
			switch ($XType) {
				case 'Technology':
					$Hardwares = Globals::getHardwareTypes();
					foreach ($Hardwares as $Hardware) {
						?><option value="<?php echo $Hardware['ID']; ?>"><?php echo $Hardware['Name']; ?></option><?php
					}
				break;
				case 'Ships':
					$Ships = AbstractSmrShip::getAllBaseShips(Globals::getGameType($ThisPlayer->getGameID()));
					Sorter::sortByNumElement($Ships, 'Name');
					foreach ($Ships as $Ship) {
						?><option value="<?php echo $Ship['ShipTypeID']; ?>"><?php echo $Ship['Name']; ?></option><?php
					}
				break;
				case 'Weapons':
					$Weapons = SmrWeapon::getAllWeapons(Globals::getGameType($ThisPlayer->getGameID()));
					Sorter::sortByNumMethod($Weapons, 'getName');
					foreach ($Weapons as $Weapon) {
						?><option value="<?php echo $Weapon->getWeaponTypeID(); ?>"><?php echo $Weapon->getName(); ?></option><?php
					}
				break;
				case 'Locations':
					?><option value="Bank">Any Bank</option>
					<option value="Bar">Any Bar</option>
					<option value="SafeFed">Any Safe Fed</option>
					<option value="Fed">Any Fed</option>
					<option value="HQ">Any Headquarters</option>
					<option value="UG">Any Underground</option>
					<option value="Hardware">Any Hardware Shop</option>
					<option value="Ship">Any Ship Shop</option>
					<option value="Weapon">Any Weapon Shop</option><?php
					$Locations = SmrLocation::getAllLocations();
					Sorter::sortByNumMethod($Locations, 'getName');
					foreach ($Locations as $Location) {
						?><option value="<?php echo $Location->getTypeID(); ?>"><?php echo $Location->getName(); ?></option><?php
					}
				break;
				case 'Sell Goods':
				case 'Buy Goods':
					$Goods = $ThisPlayer->getVisibleGoods();
					foreach ($Goods as $Good) {
						?><option value="<?php echo $Good['ID']; ?>"><?php echo $Good['Name']; ?></option><?php
					}
				break;
				case 'Galaxies':
					$Galaxies = SmrGalaxy::getGameGalaxies($ThisPlayer->getGameID());
					foreach ($Galaxies as $Galaxy) {
						?><option value="<?php echo $Galaxy->getGalaxyID(); ?>"><?php echo $Galaxy->getName(); ?></option><?php
					}
				break;
				default:
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
	foreach ($StoredDestinations as $SD) { ?>
		<div class="draggableObject savedDestination"
			style="top:<?php echo $SD['OffsetTop']; ?>px; left:<?php echo $SD['OffsetLeft']; ?>px"
			data-sector-id="<?php echo $SD['SectorID']; ?>">
			<a href="javascript:processCourse(<?php echo $SD['SectorID']; ?>)"> <?php echo '#' . $SD['SectorID'] . ' - ' . $SD['Label']; ?></a>
			<a href="javascript:processRemove(<?php echo $SD['SectorID']; ?>)"> <img src="images/silk/cross.png" width="16" height="16" alt="X" title="Delete Saved Sector"/></a>
		</div><?php
	} ?>
</div>
<?php $this->addJavascriptSource('js/course_plot.js'); ?>

<br/><br/>
<h2>Add new destination</h2>

<form class="standard" id="manageDestination" method="POST" action="<?php echo $ManageDestination; ?>">
	<label for="sectorId">Sector:</label>&nbsp;<input type="number" name="sectorId" style="width:75px" /> &nbsp; &nbsp;
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
