<a href="http://wiki.smrealms.de/index.php?title=Game_Guide:_How_your_ship_works" target="_blank"><img align="right" src="images/silk/help.png" width="16" height="16" alt="Wiki Link" title="Goto SMR Wiki: How Your Ship Works"/></a>
<form class="standard" id="PlotCourseForm" method="POST" action="<?php echo $PlotCourseFormLink; ?>">
	<h2>Conventional</h2>
	<div class="standard">Enter a destination sector.</div>
	<table class="nobord nohpad">
		<tr>
			<td>From:&nbsp;</td>
			<td><input type="number" size="5" name="from" maxlength="5" class="center" value="<?php echo $ThisPlayer->getSectorID(); ?>"></td>
			<td>&nbsp;&nbsp;&nbsp;&nbsp;To:&nbsp;</td>
			<td><input type="number" size="5" name="to" maxlength="5" class="center"></td>
			<td>&nbsp;&nbsp;&nbsp;&nbsp;<input class="submit" type="submit" name="action" value="Plot Course"></td>
		</tr>
	</table>
</form><?php

$this->includeTemplate('includes/JumpDrive.inc'); ?>

<br />
<h2>Plot To Nearest</h2>
<div class="standard">Select a location to plot to. You are only able to plot to sectors you have explored.</div><br />
<form class="standard" id="SelectXTypeForm" method="POST" action="">
	<select name="xtype" onchange="this.form.submit()"><?php
	foreach($AllXTypes as $EachXType) {
		?><option value="<?php echo $EachXType; ?>"<?php if(isset($XType)&&$EachXType==$XType){ ?> selected="selected"<?php } ?>><?php echo $EachXType; ?></option><?php
	} ?>
	</select>&nbsp;
	<input type="submit" value="Select" />
</form><?php
if(isset($XType)) { ?>
	<form class="standard" id="PlotNearestForm" method="POST" action="<?php echo $PlotNearestFormLink; ?>">
		<input type="hidden" name="xtype" value="<?php echo $XType; ?>" /><br />
		<select name="X" onchange="this.form.submit()"><?php
			switch($XType) {
				case 'Technology':
					$Hardwares =& Globals::getHardwareTypes();
					foreach($Hardwares as &$Hardware) {
						?><option value="<?php echo $Hardware['ID']; ?>"><?php echo $Hardware['Name']; ?></option><?php
					} unset($Hardware);
				break;
				case 'Ships':
					$Ships =& AbstractSmrShip::getAllBaseShips(Globals::getGameType($ThisPlayer->getGameID()));
					Sorter::sortByNumElement($Ships, 'Name');
					foreach($Ships as &$Ship) {
						?><option value="<?php echo $Ship['ShipTypeID']; ?>"><?php echo $Ship['Name']; ?></option><?php
					} unset($Ship);
				break;
				case 'Weapons':
					$Weapons =& SmrWeapon::getAllWeapons(Globals::getGameType($ThisPlayer->getGameID()));
					Sorter::sortByNumMethod($Weapons, 'getName');
					foreach($Weapons as &$Weapon) {
						?><option value="<?php echo $Weapon->getWeaponTypeID(); ?>"><?php echo $Weapon->getName(); ?></option><?php
					} unset($Weapon);
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
					$Locations =& SmrLocation::getAllLocations();
					Sorter::sortByNumMethod($Locations, 'getName');
					foreach($Locations as &$Location) {
						?><option value="<?php echo $Location->getTypeID(); ?>"><?php echo $Location->getName(); ?></option><?php
					} unset($Location);
				break;
				case 'Goods':
					$Goods =& Globals::getGoods();
					foreach($Goods as &$Good) {
						?><option value="<?php echo $Good['ID']; ?>"><?php echo $Good['Name']; ?></option><?php
					} unset($Good);
				break;
				default:
			} ?>
		</select>&nbsp;
		<input type="submit" value="Go" />
	</form><?php
} ?>